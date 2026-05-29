<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class NewsController extends Controller
{
    /**
     * Fetch news from multiple sources: NewsAPI, GNews, and RSS Feeds.
     * Merges, deduplicates, and sorts the results.
     */
    public function index(Request $request)
    {
        $page = $request->input('page', 1);
        $pageSize = $request->input('per_page', 12);
        $query = $request->input('q', '');

        $baseKeywords = ['harga pangan', 'harga bahan pokok', 'pasar tradisional', 'ketahanan pangan', 'harga beras', 'harga sayur'];
        $searchKeyword = $query ?: $baseKeywords[array_rand($baseKeywords)];
        
        $cacheKey = 'news_aggregator_' . md5($searchKeyword . $page . $pageSize);

        $data = Cache::remember($cacheKey, 1800, function () use ($searchKeyword, $page, $pageSize) {
            $articles = [];

            // 1. Fetch from RSS Feeds (Free, Unlimited, No Auth)
            // Only fetch RSS if on page 1 (RSS usually doesn't paginate well for general searches)
            if ($page == 1) {
                $rssArticles = $this->fetchFromRss();
                $articles = array_merge($articles, $rssArticles);
            }

            // 2. Fetch from NewsAPI
            $newsApiArticles = $this->fetchFromNewsApi($searchKeyword, $page, $pageSize);
            $articles = array_merge($articles, $newsApiArticles);

            // 3. Fetch from GNews
            $gnewsArticles = $this->fetchFromGNews($searchKeyword, $page, $pageSize);
            $articles = array_merge($articles, $gnewsArticles);

            // Merge, deduplicate by title, and sort
            $uniqueArticles = [];
            $titles = [];
            foreach ($articles as $article) {
                $titleLower = strtolower(trim($article['title']));
                
                // Exclude promotional content manually across all sources just in case
                $promoWords = ['promo', 'diskon', 'katalog', 'voucher', 'gratis', 'murah lebay', 'flash sale'];
                $isPromo = false;
                foreach ($promoWords as $word) {
                    if (str_contains($titleLower, $word)) {
                        $isPromo = true;
                        break;
                    }
                }

                if (!$isPromo && !isset($titles[$titleLower]) && !empty($article['title'])) {
                    $titles[$titleLower] = true;
                    $uniqueArticles[] = $article;
                }
            }

            // Sort by publishedAt DESC
            usort($uniqueArticles, function($a, $b) {
                $timeA = strtotime($a['publishedAt'] ?? '0');
                $timeB = strtotime($b['publishedAt'] ?? '0');
                return $timeB - $timeA;
            });

            // If we have no articles and it's not page 1, that's fine. If page 1 and no articles, it's an error.
            if (empty($uniqueArticles)) {
                return [
                    'status' => 'error',
                    'message' => 'Tidak ada berita yang ditemukan dari semua sumber.',
                    'articles' => []
                ];
            }

            // If we fetched RSS + API on page 1, we might have way more than $pageSize. 
            // We should slice the results to simulate pagination for the frontend.
            // However, since APIs also paginate, this is a bit tricky. 
            // We'll just return what we have (up to a generous limit) and let the frontend display it.
            $finalArticles = array_slice($uniqueArticles, 0, max($pageSize, 20));

            return [
                'status' => 'ok',
                'totalResults' => count($uniqueArticles),
                'articles' => $finalArticles
            ];
        });

        return response()->json($data);
    }

    private function fetchFromNewsApi($keyword, $page, $pageSize)
    {
        $keysStr = env('NEWS_API_KEYS', env('NEWSAPI_KEY', ''));
        $apiKeys = array_filter(array_map('trim', explode(',', $keysStr)));
        if (empty($apiKeys)) return [];

        $negativeKeywords = '-promo -diskon -katalog -voucher';
        $searchQuery = $keyword . ' ' . $negativeKeywords;

        foreach ($apiKeys as $apiKey) {
            try {
                $response = Http::timeout(5)->get('https://newsapi.org/v2/everything', [
                    'q' => $searchQuery,
                    'language' => 'id',
                    'sortBy' => 'publishedAt',
                    'pageSize' => $pageSize,
                    'page' => $page,
                    'apiKey' => $apiKey,
                ]);

                if ($response->successful()) {
                    $result = $response->json();
                    return collect($result['articles'] ?? [])->map(function ($article) {
                        return [
                            'title' => $article['title'] ?? '',
                            'description' => $article['description'] ?? '',
                            'url' => $article['url'] ?? '',
                            'urlToImage' => $article['urlToImage'] ?? null,
                            'source' => $article['source']['name'] ?? 'NewsAPI',
                            'publishedAt' => $article['publishedAt'] ?? null,
                        ];
                    })->toArray();
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        return [];
    }

    private function fetchFromGNews($keyword, $page, $pageSize)
    {
        $apiKey = env('GNEWS_API_KEY');
        if (empty($apiKey)) return [];

        try {
            $response = Http::timeout(5)->get('https://gnews.io/api/v4/search', [
                'q' => $keyword,
                'lang' => 'id',
                'country' => 'id',
                'max' => $pageSize,
                'page' => $page,
                'apikey' => $apiKey,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return collect($result['articles'] ?? [])->map(function ($article) {
                    return [
                        'title' => $article['title'] ?? '',
                        'description' => $article['description'] ?? '',
                        'url' => $article['url'] ?? '',
                        'urlToImage' => $article['image'] ?? null,
                        'source' => $article['source']['name'] ?? 'GNews',
                        'publishedAt' => $article['publishedAt'] ?? null,
                    ];
                })->toArray();
            }
        } catch (\Exception $e) {
            return [];
        }
        return [];
    }

    private function fetchFromRss()
    {
        // RSS feeds from major Indonesian news portals (Ekonomi/Bisnis sections)
        $feeds = [
            'Antara News' => 'https://www.antaranews.com/rss/ekonomi-bisnis.xml',
            'Suara.com' => 'https://www.suara.com/rss/bisnis',
            'Republika' => 'https://www.republika.co.id/rss/ekonomi',
        ];

        $articles = [];

        foreach ($feeds as $sourceName => $url) {
            try {
                $xmlString = Http::timeout(5)->get($url)->body();
                // Suppress XML parsing warnings
                $xml = @simplexml_load_string($xmlString, 'SimpleXMLElement', LIBXML_NOCDATA);
                
                if ($xml && isset($xml->channel->item)) {
                    $count = 0;
                    foreach ($xml->channel->item as $item) {
                        if ($count >= 10) break; // limit items per feed
                        
                        $desc = strip_tags((string)$item->description);
                        // Some feeds put images in <enclosure> or <media:content>
                        $imageUrl = null;
                        if (isset($item->enclosure) && isset($item->enclosure['url'])) {
                            $imageUrl = (string)$item->enclosure['url'];
                        }

                        $articles[] = [
                            'title' => (string)$item->title,
                            'description' => $desc,
                            'url' => (string)$item->link,
                            'urlToImage' => $imageUrl,
                            'source' => $sourceName,
                            'publishedAt' => date('Y-m-d\TH:i:s\Z', strtotime((string)$item->pubDate)),
                        ];
                        $count++;
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return $articles;
    }
}
