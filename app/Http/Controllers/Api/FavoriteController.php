<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index()
    {
        $user = auth('api')->user();
        $favorites = Favorite::where('user_id', $user->id)
            ->with('product.category')
            ->get()
            ->map(function ($fav) {
                $product = $fav->product;

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

                $latestPrice = $product->prices()
                    ->where('is_suspicious', false)
                    ->latest()
                    ->first();

                $arr = $product->toArray();
                $arr['average_price'] = $avgPrice ? round($avgPrice, 0) : null;
                $arr['current_price'] = $latestPrice ? $latestPrice->price : null;

                return $arr;
            });

        return response()->json(['favorites' => $favorites]);
    }

    public function toggle($productId)
    {
        $user = auth('api')->user();
        Product::findOrFail($productId);

        $existing = Favorite::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['message' => 'Produk dihapus dari favorit', 'is_favorite' => false]);
        }

        Favorite::create(['user_id' => $user->id, 'product_id' => $productId]);
        return response()->json(['message' => 'Produk ditambahkan ke favorit', 'is_favorite' => true], 201);
    }
}
