<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Serealia', 'slug' => 'serealia', 'icon' => '🌾', 'description' => 'Beras, jagung, gandum, dan serealia lainnya'],
            ['name' => 'Daging', 'slug' => 'daging', 'icon' => '🥩', 'description' => 'Daging sapi, ayam, kambing, dan lainnya'],
            ['name' => 'Ikan & Hasil Laut', 'slug' => 'ikan-hasil-laut', 'icon' => '🐟', 'description' => 'Ikan, udang, cumi, dan hasil laut lainnya'],
            ['name' => 'Telur & Produk Susu', 'slug' => 'telur-produk-susu', 'icon' => '🥚', 'description' => 'Telur ayam, telur bebek, susu'],
            ['name' => 'Buah-buahan', 'slug' => 'buah-buahan', 'icon' => '🍎', 'description' => 'Pisang, apel, jeruk, dan buah lainnya'],
            ['name' => 'Rempah', 'slug' => 'rempah', 'icon' => '🌶️', 'description' => 'Cabai, bawang merah, bawang putih, jahe'],
            ['name' => 'Sayur & Jamur', 'slug' => 'sayur-jamur', 'icon' => '🥬', 'description' => 'Kangkung, bayam, jamur tiram, dan lainnya'],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }
    }
}
