<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            'serealia' => [
                ['name' => 'Beras', 'slug' => 'beras', 'unit' => 'kg'],
                ['name' => 'Jagung', 'slug' => 'jagung', 'unit' => 'kg'],
                ['name' => 'Gandum', 'slug' => 'gandum', 'unit' => 'kg'],
                ['name' => 'Sorgum', 'slug' => 'sorgum', 'unit' => 'kg'],
            ],
            'daging' => [
                ['name' => 'Daging Sapi', 'slug' => 'daging-sapi', 'unit' => 'kg'],
                ['name' => 'Daging Ayam', 'slug' => 'daging-ayam', 'unit' => 'kg'],
                ['name' => 'Daging Kambing', 'slug' => 'daging-kambing', 'unit' => 'kg'],
            ],
            'ikan-hasil-laut' => [
                ['name' => 'Ikan Lele', 'slug' => 'ikan-lele', 'unit' => 'kg'],
                ['name' => 'Ikan Tuna', 'slug' => 'ikan-tuna', 'unit' => 'kg'],
                ['name' => 'Udang', 'slug' => 'udang', 'unit' => 'kg'],
                ['name' => 'Cumi', 'slug' => 'cumi', 'unit' => 'kg'],
            ],
            'telur-produk-susu' => [
                ['name' => 'Telur Ayam', 'slug' => 'telur-ayam', 'unit' => 'butir'],
                ['name' => 'Telur Bebek', 'slug' => 'telur-bebek', 'unit' => 'butir'],
                ['name' => 'Susu', 'slug' => 'susu', 'unit' => 'liter'],
            ],
            'buah-buahan' => [
                ['name' => 'Pisang', 'slug' => 'pisang', 'unit' => 'kg'],
                ['name' => 'Apel', 'slug' => 'apel', 'unit' => 'kg'],
                ['name' => 'Jeruk', 'slug' => 'jeruk', 'unit' => 'kg'],
            ],
            'rempah' => [
                ['name' => 'Cabai', 'slug' => 'cabai', 'unit' => 'kg'],
                ['name' => 'Bawang Merah', 'slug' => 'bawang-merah', 'unit' => 'kg'],
                ['name' => 'Bawang Putih', 'slug' => 'bawang-putih', 'unit' => 'kg'],
                ['name' => 'Jahe', 'slug' => 'jahe', 'unit' => 'kg'],
            ],
            'sayur-jamur' => [
                ['name' => 'Kangkung', 'slug' => 'kangkung', 'unit' => 'ikat'],
                ['name' => 'Bayam', 'slug' => 'bayam', 'unit' => 'ikat'],
                ['name' => 'Jamur Tiram', 'slug' => 'jamur-tiram', 'unit' => 'kg'],
            ],
        ];

        foreach ($products as $categorySlug => $items) {
            $category = Category::where('slug', $categorySlug)->first();
            foreach ($items as $product) {
                Product::create(array_merge($product, ['category_id' => $category->id]));
            }
        }
    }
}
