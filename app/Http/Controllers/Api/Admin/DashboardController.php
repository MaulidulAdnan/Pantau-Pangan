<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Price;
use App\Models\Comment;
use App\Models\Report;
use App\Models\MerchantProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats()
    {
        return response()->json([
            'total_users' => User::where('role', 'user')->count(),
            'total_merchants' => User::where('role', 'pedagang')->count(),
            'approved_merchants' => MerchantProfile::where('status', 'approved')->count(),
            'pending_merchants' => MerchantProfile::where('status', 'pending')->count(),
            'pending_stores' => \App\Models\MerchantStore::where('status', 'pending')->count(),
            'total_products' => Product::count(),
            'total_prices' => Price::count(),
            'suspicious_prices' => Price::where('is_suspicious', true)->count(),
            'total_comments' => Comment::where('is_deleted', false)->count(),
            'pending_reports' => Report::where('status', 'pending')->count(),
        ]);
    }

    public function analytics()
    {
        // Popular products (most price entries)
        $popularProducts = Product::withCount('prices')
            ->orderByDesc('prices_count')
            ->limit(10)
            ->get();

        // Regions with highest average prices
        $regionPrices = DB::table('prices')
            ->join('markets', 'prices.market_id', '=', 'markets.id')
            ->join('regions', 'markets.region_id', '=', 'regions.id')
            ->where('prices.is_suspicious', false)
            ->where('prices.created_at', '>=', now()->subDays(30))
            ->select('regions.name', DB::raw('AVG(prices.price) as avg_price'))
            ->groupBy('regions.name')
            ->orderByDesc('avg_price')
            ->limit(10)
            ->get();

        // Daily new users (last 30 days)
        $newUsers = User::where('created_at', '>=', now()->subDays(30))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Price trend (last 30 days)
        $priceTrend = Price::where('is_suspicious', false)
            ->where('created_at', '>=', now()->subDays(30))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('AVG(price) as avg_price'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'popular_products' => $popularProducts,
            'region_prices' => $regionPrices,
            'new_users' => $newUsers,
            'price_trend' => $priceTrend,
        ]);
    }

    public function exportPrices()
    {
        $prices = DB::table('prices')
            ->join('products', 'prices.product_id', '=', 'products.id')
            ->join('markets', 'prices.market_id', '=', 'markets.id')
            ->join('regions', 'markets.region_id', '=', 'regions.id')
            ->leftJoin('merchant_stores', 'prices.store_id', '=', 'merchant_stores.id')
            ->select(
                'products.name as product',
                'products.unit',
                'prices.price',
                'merchant_stores.store_name',
                'markets.name as market',
                'regions.name as region',
                'prices.stock_status',
                'prices.created_at'
            )
            ->where('prices.is_suspicious', false)
            ->orderBy('prices.created_at', 'desc')
            ->limit(1000)
            ->get();

        $csvData = "Produk,Satuan,Harga,Toko,Pasar,Daerah,Status Stok,Tanggal\n";
        foreach ($prices as $row) {
            $csvData .= sprintf(
                "\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
                $row->product,
                $row->unit,
                $row->price,
                $row->store_name ?? '-',
                $row->market,
                $row->region,
                $row->stock_status,
                $row->created_at
            );
        }

        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="laporan_harga_' . date('Ymd_His') . '.csv"');
    }

    public function broadcast(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'target' => 'required|in:all,merchants'
        ]);

        $query = User::query();
        if ($request->target === 'merchants') {
            $query->where('role', 'pedagang');
        }

        $users = $query->get();
        
        foreach ($users as $user) {
            $user->notify(new \App\Notifications\SystemBroadcast($request->message));
        }

        return response()->json(['message' => 'Broadcast berhasil dikirim ke ' . $users->count() . ' pengguna.']);
    }
}
