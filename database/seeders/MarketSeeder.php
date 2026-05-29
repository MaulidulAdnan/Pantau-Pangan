<?php

namespace Database\Seeders;

use App\Models\Market;
use App\Models\Region;
use Illuminate\Database\Seeder;

class MarketSeeder extends Seeder
{
    public function run(): void
    {
        $markets = [
            'jakarta' => [
                ['name' => 'Pasar Tanah Abang', 'slug' => 'pasar-tanah-abang', 'address' => 'Jl. Jatibaru Raya, Tanah Abang'],
                ['name' => 'Pasar Senen', 'slug' => 'pasar-senen', 'address' => 'Jl. Pasar Senen, Senen'],
                ['name' => 'Pasar Kramat Jati', 'slug' => 'pasar-kramat-jati', 'address' => 'Jl. Raya Bogor, Kramat Jati'],
            ],
            'bandung' => [
                ['name' => 'Pasar Baru Bandung', 'slug' => 'pasar-baru-bandung', 'address' => 'Jl. Otto Iskandar Dinata, Bandung'],
                ['name' => 'Pasar Kosambi', 'slug' => 'pasar-kosambi', 'address' => 'Jl. Ahmad Yani, Bandung'],
            ],
            'surabaya' => [
                ['name' => 'Pasar Pabean', 'slug' => 'pasar-pabean', 'address' => 'Jl. Kembang Jepun, Surabaya'],
                ['name' => 'Pasar Keputran', 'slug' => 'pasar-keputran', 'address' => 'Jl. Keputran, Surabaya'],
            ],
            'yogyakarta' => [
                ['name' => 'Pasar Beringharjo', 'slug' => 'pasar-beringharjo', 'address' => 'Jl. Margo Mulyo, Yogyakarta'],
                ['name' => 'Pasar Kranggan', 'slug' => 'pasar-kranggan', 'address' => 'Jl. Kranggan, Yogyakarta'],
            ],
            'semarang' => [
                ['name' => 'Pasar Johar', 'slug' => 'pasar-johar', 'address' => 'Jl. Agus Salim, Semarang'],
                ['name' => 'Pasar Bulu', 'slug' => 'pasar-bulu', 'address' => 'Jl. Sultan Agung, Semarang'],
            ],
        ];

        foreach ($markets as $regionSlug => $items) {
            $region = Region::where('slug', $regionSlug)->first();
            foreach ($items as $market) {
                Market::create(array_merge($market, ['region_id' => $region->id]));
            }
        }
    }
}
