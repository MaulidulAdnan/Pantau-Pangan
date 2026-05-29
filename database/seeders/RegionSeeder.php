<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            ['name' => 'Jakarta', 'slug' => 'jakarta', 'province' => 'DKI Jakarta'],
            ['name' => 'Bandung', 'slug' => 'bandung', 'province' => 'Jawa Barat'],
            ['name' => 'Surabaya', 'slug' => 'surabaya', 'province' => 'Jawa Timur'],
            ['name' => 'Yogyakarta', 'slug' => 'yogyakarta', 'province' => 'DI Yogyakarta'],
            ['name' => 'Semarang', 'slug' => 'semarang', 'province' => 'Jawa Tengah'],
        ];

        foreach ($regions as $region) {
            Region::create($region);
        }
    }
}
