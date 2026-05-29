<?php

namespace Database\Seeders;

use App\Models\Price;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PriceSeeder extends Seeder
{
    public function run(): void
    {
        $merchants = User::where('role', 'pedagang')->pluck('id')->toArray();

        // Base prices for products (realistic Indonesian prices in Rupiah)
        $basePrices = [
            'beras' => 14000, 'jagung' => 8000, 'gandum' => 12000, 'sorgum' => 10000,
            'daging-sapi' => 130000, 'daging-ayam' => 35000, 'daging-kambing' => 120000,
            'ikan-lele' => 28000, 'ikan-tuna' => 55000, 'udang' => 85000, 'cumi' => 65000,
            'telur-ayam' => 2800, 'telur-bebek' => 3500, 'susu' => 18000,
            'pisang' => 12000, 'apel' => 35000, 'jeruk' => 20000,
            'cabai' => 40000, 'bawang-merah' => 35000, 'bawang-putih' => 32000, 'jahe' => 28000,
            'kangkung' => 5000, 'bayam' => 6000, 'jamur-tiram' => 25000,
        ];

        $marketIds = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];

        foreach ($basePrices as $slug => $basePrice) {
            $product = Product::where('slug', $slug)->first();
            if (!$product) continue;

            // Generate 30 days of data
            for ($day = 30; $day >= 0; $day--) {
                $date = Carbon::now()->subDays($day);

                // 2-4 price entries per day from different merchants/markets
                $entriesPerDay = rand(2, 4);
                for ($e = 0; $e < $entriesPerDay; $e++) {
                    // Random variation: -10% to +15%
                    $variation = $basePrice * (rand(-10, 15) / 100);
                    // Add slight upward trend for some products
                    $trend = ($day < 15 && in_array($slug, ['cabai', 'bawang-merah', 'daging-ayam']))
                        ? $basePrice * 0.05
                        : 0;

                    $price = max(500, round(($basePrice + $variation + $trend) / 100) * 100);

                    Price::create([
                        'product_id' => $product->id,
                        'market_id' => $marketIds[array_rand($marketIds)],
                        'user_id' => $merchants[array_rand($merchants)],
                        'price' => $price,
                        'stock_status' => $this->randomStock(),
                        'is_suspicious' => false,
                        'created_at' => $date->copy()->addHours(rand(6, 18))->addMinutes(rand(0, 59)),
                        'updated_at' => $date->copy()->addHours(rand(6, 18))->addMinutes(rand(0, 59)),
                    ]);
                }
            }
        }
    }

    private function randomStock(): string
    {
        $rand = rand(1, 100);
        if ($rand <= 80) return 'available';
        if ($rand <= 95) return 'scarce';
        return 'out_of_stock';
    }
}
