<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
            RegionSeeder::class,
            MarketSeeder::class,
            UserSeeder::class,
            PriceSeeder::class,
            CommentSeeder::class,
        ]);
    }
}
