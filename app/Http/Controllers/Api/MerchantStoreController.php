<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MerchantStore;
use App\Models\Market;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MerchantStoreController extends Controller
{
    /**
     * List all stores belonging to the authenticated merchant
     */
    public function index()
    {
        $user = auth('api')->user();
        $stores = MerchantStore::where('user_id', $user->id)
            ->with('market.region', 'region')
            ->get();

        return response()->json(['stores' => $stores]);
    }

    /**
     * Request to add a new store (requires admin approval)
     * Constraint: all stores must be in the same region
     */
    public function store(Request $request)
    {
        $user = auth('api')->user();

        $validator = Validator::make($request->all(), [
            'store_name' => 'required|string|max:255',
            'store_address' => 'nullable|string|max:500',
            'market_id' => 'required|exists:markets,id',
            'shop_photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'shop_photo.required' => 'Foto toko wajib diunggah',
            'shop_photo.image' => 'File harus berupa gambar',
            'shop_photo.mimes' => 'Format gambar harus JPEG, PNG, atau WebP',
            'shop_photo.max' => 'Ukuran foto maksimal 2MB',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Get market's region
        $market = Market::with('region')->findOrFail($request->market_id);
        $regionId = $market->region_id;

        // Check if merchant already has stores - must be in the same region
        $existingStore = MerchantStore::where('user_id', $user->id)->first();
        if ($existingStore && $existingStore->region_id !== $regionId) {
            return response()->json([
                'message' => 'Semua toko Anda harus berada di daerah yang sama (' . Region::find($existingStore->region_id)->name . ')',
            ], 422);
        }

        $shopPhotoPath = null;
        if ($request->hasFile('shop_photo')) {
            $shopPhotoPath = $request->file('shop_photo')->store('shop-photos', 'public');
        }

        $store = MerchantStore::create([
            'user_id' => $user->id,
            'region_id' => $regionId,
            'market_id' => $request->market_id,
            'store_name' => $request->store_name,
            'store_address' => $request->store_address,
            'shop_photo' => $shopPhotoPath,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Pengajuan toko baru berhasil dikirim. Menunggu verifikasi admin.',
            'store' => $store->load('market.region'),
        ], 201);
    }
}
