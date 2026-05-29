<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MerchantProfile;
use App\Models\MerchantStore;
use Illuminate\Http\Request;

class MerchantController extends Controller
{
    public function pending()
    {
        $merchants = MerchantProfile::where('status', 'pending')
            ->with('user', 'market.region')
            ->latest()
            ->paginate(15);

        return response()->json($merchants);
    }

    public function approve($userId)
    {
        $profile = MerchantProfile::where('user_id', $userId)->firstOrFail();
        $profile->update([
            'status' => 'approved',
            'verified_at' => now(),
        ]);

        // Also create their first store from merchant_profile data
        $existingStore = MerchantStore::where('user_id', $userId)->first();
        if (!$existingStore) {
            $market = \App\Models\Market::find($profile->market_id);
            if ($market) {
                MerchantStore::create([
                    'user_id' => $userId,
                    'region_id' => $market->region_id,
                    'market_id' => $profile->market_id,
                    'store_name' => $profile->store_name,
                    'store_address' => $profile->store_address,
                    'shop_photo' => $profile->shop_photo,
                    'status' => 'approved',
                ]);
            }
        }

        return response()->json(['message' => 'Pedagang berhasil diverifikasi', 'merchant' => $profile->load('user')]);
    }

    public function reject($userId)
    {
        $profile = MerchantProfile::where('user_id', $userId)->firstOrFail();
        $profile->update(['status' => 'rejected']);

        return response()->json(['message' => 'Pedagang ditolak', 'merchant' => $profile->load('user')]);
    }

    public function all()
    {
        $merchants = MerchantProfile::with('user', 'market.region')->latest()->paginate(15);
        return response()->json($merchants);
    }

    // ===== Store Management =====

    public function pendingStores()
    {
        $stores = MerchantStore::where('status', 'pending')
            ->with('user', 'market.region', 'region')
            ->latest()
            ->paginate(15);

        return response()->json($stores);
    }

    public function approveStore($storeId)
    {
        $store = MerchantStore::findOrFail($storeId);
        $store->update(['status' => 'approved']);
        return response()->json(['message' => 'Toko berhasil disetujui', 'store' => $store->load('user', 'market')]);
    }

    public function rejectStore($storeId)
    {
        $store = MerchantStore::findOrFail($storeId);
        $store->update(['status' => 'rejected']);
        return response()->json(['message' => 'Pengajuan toko ditolak', 'store' => $store->load('user', 'market')]);
    }
}
