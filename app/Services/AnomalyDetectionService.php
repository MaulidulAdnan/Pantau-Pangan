<?php

namespace App\Services;

use App\Models\Price;
use App\Models\User;
use App\Notifications\PriceAnomalyNotification;

class AnomalyDetectionService
{
    public function checkAnomaly(Price $price): bool
    {
        $avgPrice = Price::where('product_id', $price->product_id)
            ->where('market_id', $price->market_id)
            ->where('is_suspicious', false)
            ->where('id', '!=', $price->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->avg('price');

        if (!$avgPrice) {
            $avgPrice = Price::where('product_id', $price->product_id)
                ->where('is_suspicious', false)
                ->where('id', '!=', $price->id)
                ->where('created_at', '>=', now()->subDays(30))
                ->avg('price');
        }

        if (!$avgPrice) {
            return false;
        }

        $upperThreshold = $avgPrice * 3;
        $lowerThreshold = $avgPrice * 0.3;
        $isSuspicious = $price->price > $upperThreshold || $price->price < $lowerThreshold;

        if ($isSuspicious) {
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new PriceAnomalyNotification($price, $avgPrice));
            }
        }

        return $isSuspicious;
    }
}
