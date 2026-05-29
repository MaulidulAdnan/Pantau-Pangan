<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Price;
use Illuminate\Http\Request;

class PriceModerationController extends Controller
{
    public function suspicious()
    {
        $prices = Price::where('is_suspicious', true)
            ->with('product', 'market.region', 'user')
            ->latest()
            ->paginate(15);

        return response()->json($prices);
    }

    public function deletePrice($priceId)
    {
        $price = Price::findOrFail($priceId);
        $price->delete();
        return response()->json(['message' => 'Harga anomali berhasil dihapus']);
    }

    public function markSuspicious($priceId)
    {
        $price = Price::findOrFail($priceId);
        $price->update(['is_suspicious' => true]);
        return response()->json(['message' => 'Harga ditandai sebagai suspicious']);
    }

    public function clearSuspicious($priceId)
    {
        $price = Price::findOrFail($priceId);
        $price->update(['is_suspicious' => false]);
        return response()->json(['message' => 'Status suspicious dihapus']);
    }
}
