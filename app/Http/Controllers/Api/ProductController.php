<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        // Filter by category
        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Filter by region
        if ($request->has('region')) {
            $query->whereHas('prices.market.region', function ($q) use ($request) {
                $q->where('slug', $request->region);
            });
        }

        // Filter by market
        if ($request->has('market')) {
            $query->whereHas('prices.market', function ($q) use ($request) {
                $q->where('slug', $request->market);
            });
        }

        // Search
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->paginate(20);

        // Append latest price info as explicit array keys so they appear in JSON
        $products->getCollection()->transform(function ($product) {
            $latestPrice = $product->prices()
                ->where('is_suspicious', false)
                ->latest()
                ->first();

            // Try 7-day average first, fall back to all-time average
            $avgPrice = $product->prices()
                ->where('is_suspicious', false)
                ->where('created_at', '>=', now()->subDays(7))
                ->avg('price');

            if (!$avgPrice) {
                $avgPrice = $product->prices()
                    ->where('is_suspicious', false)
                    ->avg('price');
            }

            $prevAvgPrice = $product->prices()
                ->where('is_suspicious', false)
                ->whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])
                ->avg('price');

            if (!$prevAvgPrice) {
                $prevAvgPrice = $product->prices()
                    ->where('is_suspicious', false)
                    ->where('created_at', '<', now()->subDays(7))
                    ->avg('price');
            }

            $arr = $product->toArray();
            $arr['current_price'] = $latestPrice ? $latestPrice->price : null;
            $arr['average_price'] = $avgPrice ? round($avgPrice, 0) : null;
            $arr['price_change'] = ($avgPrice && $prevAvgPrice)
                ? round((($avgPrice - $prevAvgPrice) / $prevAvgPrice) * 100, 1)
                : null;
            $arr['stock_status'] = $latestPrice ? $latestPrice->stock_status : null;
            $arr['last_updated'] = $latestPrice ? $latestPrice->created_at : null;

            return $arr;
        });

        return response()->json($products);
    }

    public function show($slug)
    {
        $product = Product::where('slug', $slug)
            ->with('category')
            ->firstOrFail();

        // Current prices per market
        $pricesByMarket = $product->prices()
            ->where('is_suspicious', false)
            ->with('market.region', 'user')
            ->latest()
            ->get()
            ->groupBy('market_id')
            ->map(function ($prices) {
                return $prices->first();
            })
            ->values();

        // Average price (7-day, fallback to all-time)
        $avgPrice = $product->prices()
            ->where('is_suspicious', false)
            ->where('created_at', '>=', now()->subDays(7))
            ->avg('price');

        if (!$avgPrice) {
            $avgPrice = $product->prices()
                ->where('is_suspicious', false)
                ->avg('price');
        }

        // Previous week average (fallback to older data)
        $prevAvgPrice = $product->prices()
            ->where('is_suspicious', false)
            ->whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])
            ->avg('price');

        if (!$prevAvgPrice) {
            $prevAvgPrice = $product->prices()
                ->where('is_suspicious', false)
                ->where('created_at', '<', now()->subDays(7))
                ->avg('price');
        }

        $priceChange = ($avgPrice && $prevAvgPrice)
            ? round((($avgPrice - $prevAvgPrice) / $prevAvgPrice) * 100, 1)
            : null;

        return response()->json([
            'product' => $product,
            'prices_by_market' => $pricesByMarket,
            'average_price' => $avgPrice ? round($avgPrice, 0) : null,
            'price_change' => $priceChange,
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json(['products' => []]);
        }

        $products = Product::where('name', 'like', '%' . $query . '%')
            ->with('category')
            ->limit(10)
            ->get();

        return response()->json(['products' => $products]);
    }

    /**
     * Create a new product (pedagang adds a product not yet in the list)
     */
    public function createProduct(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'unit' => 'required|in:kg,liter,butir,ikat',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if product with similar name already exists
        $existing = Product::where('name', 'like', $request->name)->first();
        if ($existing) {
            return response()->json([
                'message' => 'Produk dengan nama serupa sudah ada',
                'product' => $existing,
            ], 422);
        }

        $slug = \Illuminate\Support\Str::slug($request->name);
        // Ensure slug uniqueness
        $count = Product::where('slug', 'like', $slug . '%')->count();
        if ($count > 0) {
            $slug = $slug . '-' . ($count + 1);
        }

        $product = Product::create([
            'name' => $request->name,
            'slug' => $slug,
            'category_id' => $request->category_id,
            'unit' => $request->unit,
        ]);

        return response()->json([
            'message' => 'Produk baru berhasil ditambahkan',
            'product' => $product->load('category'),
        ], 201);
    }
}
