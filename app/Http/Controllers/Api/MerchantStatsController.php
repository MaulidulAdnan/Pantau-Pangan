<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MerchantProfile;
use App\Models\Price;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MerchantStatsController extends Controller
{
    public function stats()
    {
        $user = auth('api')->user();
        $profile = $user->merchantProfile;

        if (!$profile) {
            return response()->json(['message' => 'Profil pedagang tidak ditemukan'], 404);
        }

        $totalPrices = Price::where('user_id', $user->id)->count();
        $validPrices = Price::where('user_id', $user->id)->where('is_suspicious', false)->count();
        $suspiciousPrices = Price::where('user_id', $user->id)->where('is_suspicious', true)->count();

        $recentPrices = Price::where('user_id', $user->id)
            ->with('product', 'market')
            ->latest()
            ->limit(10)
            ->get();

        $productContributions = Price::where('user_id', $user->id)
            ->select('product_id', DB::raw('COUNT(*) as count'))
            ->groupBy('product_id')
            ->with('product')
            ->get();

        return response()->json([
            'profile' => $profile->load('market.region'),
            'total_contributions' => $totalPrices,
            'valid_contributions' => $validPrices,
            'suspicious_count' => $suspiciousPrices,
            'recent_prices' => $recentPrices,
            'product_contributions' => $productContributions,
        ]);
    }
}
