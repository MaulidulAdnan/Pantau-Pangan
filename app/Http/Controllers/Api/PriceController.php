<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Price;
use App\Models\Product;
use App\Services\AnomalyDetectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PriceController extends Controller
{
    protected $anomalyService;

    public function __construct(AnomalyDetectionService $anomalyService)
    {
        $this->anomalyService = $anomalyService;
    }

    /**
     * Get prices for a product
     */
    public function index(Request $request, $productId)
    {
        $query = Price::where('product_id', $productId)
            ->where('is_suspicious', false)
            ->with('market.region', 'user');

        if ($request->has('region')) {
            $query->whereHas('market.region', function ($q) use ($request) {
                $q->where('slug', $request->region);
            });
        }

        if ($request->has('market')) {
            $query->whereHas('market', function ($q) use ($request) {
                $q->where('slug', $request->market);
            });
        }

        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        $prices = $query->latest()->paginate(20);

        return response()->json($prices);
    }

    /**
     * Store new price (pedagang only)
     */
    public function store(Request $request)
    {
        $user = auth('api')->user();

        // Check if pedagang is approved
        if (!$user->isApprovedMerchant()) {
            return response()->json(['message' => 'Akun pedagang belum diverifikasi admin'], 403);
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'market_id' => 'required|exists:markets,id',
            'store_id' => 'required|exists:merchant_stores,id',
            'price' => 'required|numeric|min:100',
            'stock_status' => 'required|in:available,scarce,out_of_stock',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if a price for this product and STORE already exists TODAY
        $price = Price::where('product_id', $request->product_id)
            ->where('store_id', $request->store_id)
            ->whereDate('created_at', now()->toDateString())
            ->first();

        if ($price) {
            // Update today's existing record instead of creating duplicate
            $price->update([
                'price' => $request->price,
                'stock_status' => $request->stock_status,
                'notes' => $request->notes,
                'is_suspicious' => false // reset and recheck below
            ]);
        } else {
            // Create new daily record
            $price = Price::create([
                'product_id' => $request->product_id,
                'market_id' => $request->market_id,
                'store_id' => $request->store_id,
                'user_id' => $user->id,
                'price' => $request->price,
                'stock_status' => $request->stock_status,
                'notes' => $request->notes,
            ]);
            
            // Update merchant contributions for new entries
            $user->merchantProfile->increment('total_contributions');
        }

        // Check for anomaly
        $isSuspicious = $this->anomalyService->checkAnomaly($price);
        if ($isSuspicious) {
            $price->update(['is_suspicious' => true]);
        } else if ($price->wasRecentlyCreated) {
            $user->merchantProfile->increment('valid_contributions');
        }

        return response()->json([
            'message' => $isSuspicious
                ? 'Harga berhasil ditambahkan, namun ditandai untuk review admin karena terdeteksi anomali'
                : 'Harga berhasil disimpan',
            'price' => $price->load('product', 'market'),
            'is_suspicious' => $isSuspicious,
        ], 201);
    }

    /**
     * Update price (pedagang, own data only)
     */
    public function update(Request $request, $id)
    {
        $user = auth('api')->user();
        $price = Price::findOrFail($id);

        if ($price->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['message' => 'Anda hanya dapat mengubah harga milik sendiri'], 403);
        }

        $validator = Validator::make($request->all(), [
            'price' => 'sometimes|numeric|min:100',
            'stock_status' => 'sometimes|in:available,scarce,out_of_stock',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $price->update($request->only(['price', 'stock_status', 'notes']));

        // Re-check anomaly if price changed
        if ($request->has('price')) {
            $isSuspicious = $this->anomalyService->checkAnomaly($price);
            $price->update(['is_suspicious' => $isSuspicious]);
        }

        return response()->json([
            'message' => 'Harga berhasil diperbarui',
            'price' => $price->load('product', 'market'),
        ]);
    }

    /**
     * Delete price
     */
    public function destroy($id)
    {
        $user = auth('api')->user();
        $price = Price::findOrFail($id);

        if ($price->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['message' => 'Anda hanya dapat menghapus harga milik sendiri'], 403);
        }

        $price->delete();

        return response()->json(['message' => 'Harga berhasil dihapus']);
    }

    /**
     * Get average prices for a product
     */
    public function average($productId)
    {
        $product = Product::findOrFail($productId);

        $weeklyAvg = Price::where('product_id', $productId)
            ->where('is_suspicious', false)
            ->where('created_at', '>=', now()->subDays(7))
            ->avg('price');

        $monthlyAvg = Price::where('product_id', $productId)
            ->where('is_suspicious', false)
            ->where('created_at', '>=', now()->subDays(30))
            ->avg('price');

        $prevWeekAvg = Price::where('product_id', $productId)
            ->where('is_suspicious', false)
            ->whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])
            ->avg('price');

        $priceChange = ($weeklyAvg && $prevWeekAvg)
            ? round((($weeklyAvg - $prevWeekAvg) / $prevWeekAvg) * 100, 1)
            : null;

        // Min and max
        $minPrice = Price::where('product_id', $productId)
            ->where('is_suspicious', false)
            ->where('created_at', '>=', now()->subDays(30))
            ->min('price');

        $maxPrice = Price::where('product_id', $productId)
            ->where('is_suspicious', false)
            ->where('created_at', '>=', now()->subDays(30))
            ->max('price');

        return response()->json([
            'product' => $product->name,
            'unit' => $product->unit,
            'weekly_average' => $weeklyAvg ? round($weeklyAvg, 0) : null,
            'monthly_average' => $monthlyAvg ? round($monthlyAvg, 0) : null,
            'price_change_percent' => $priceChange,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
        ]);
    }

    /**
     * Get chart data for a product
     */
    public function chart(Request $request, $productId)
    {
        $period = $request->get('period', 'daily'); // daily, weekly, monthly
        $product = Product::findOrFail($productId);

        $query = Price::where('product_id', $productId)
            ->where('is_suspicious', false);

        if ($request->has('region') && $request->region) {
            $query->whereHas('market.region', function ($q) use ($request) {
                $q->where('slug', $request->region);
            });
        }

        switch ($period) {
            case 'daily':
                $data = $query->where('created_at', '>=', now()->subDays(30))
                    ->select(
                        DB::raw('DATE(created_at) as date'),
                        DB::raw('AVG(price) as avg_price'),
                        DB::raw('MIN(price) as min_price'),
                        DB::raw('MAX(price) as max_price'),
                        DB::raw('COUNT(*) as total_data')
                    )
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
                break;

            case 'weekly':
                $data = $query->where('created_at', '>=', now()->subMonths(3))
                    ->select(
                        DB::raw('YEARWEEK(created_at) as week'),
                        DB::raw('MIN(DATE(created_at)) as start_date'),
                        DB::raw('AVG(price) as avg_price'),
                        DB::raw('MIN(price) as min_price'),
                        DB::raw('MAX(price) as max_price'),
                        DB::raw('COUNT(*) as total_data')
                    )
                    ->groupBy('week')
                    ->orderBy('week')
                    ->get();
                break;

            case 'monthly':
                $data = $query->where('created_at', '>=', now()->subYear())
                    ->select(
                        DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                        DB::raw('AVG(price) as avg_price'),
                        DB::raw('MIN(price) as min_price'),
                        DB::raw('MAX(price) as max_price'),
                        DB::raw('COUNT(*) as total_data')
                    )
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get();
                break;

            default:
                return response()->json(['message' => 'Period tidak valid'], 422);
        }

        return response()->json([
            'product' => $product->name,
            'period' => $period,
            'chart_data' => $data,
        ]);
    }

    /**
     * Merchant price history (own prices only, with filters)
     */
    public function history(Request $request)
    {
        $user = auth('api')->user();

        // Get the latest price IDs for each product-market-store combination
        $latestPricesSubquery = Price::select(DB::raw('MAX(id) as max_id'))
            ->where('user_id', $user->id)
            ->where('is_suspicious', false)
            ->groupBy('product_id', 'market_id', 'store_id');

        $query = Price::whereIn('prices.id', $latestPricesSubquery)
            ->with('product.category', 'market.region', 'store');

        // Search by product name
        if ($request->has('search') && $request->search) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->whereHas('product.category', function ($q) use ($request) {
                $q->where('id', $request->category);
            });
        }

        // Only show prices from merchant's own stores
        $storeMarketIds = \App\Models\MerchantStore::where('user_id', $user->id)
            ->where('status', 'approved')
            ->pluck('market_id')
            ->toArray();

        if (!empty($storeMarketIds)) {
            $query->whereIn('market_id', $storeMarketIds);
        }

        // Sort by product name for better catalog view
        $prices = $query->join('products', 'prices.product_id', '=', 'products.id')
            ->select('prices.*')
            ->orderBy('products.name', 'asc')
            ->paginate(50);

        return response()->json($prices);
    }

    /**
     * Merchant's suspicious/anomaly prices
     */
    public function suspicious(Request $request)
    {
        $user = auth('api')->user();

        $prices = Price::where('user_id', $user->id)
            ->where('is_suspicious', true)
            ->with('product.category', 'market.region')
            ->latest()
            ->paginate(20);

        return response()->json($prices);
    }
}
