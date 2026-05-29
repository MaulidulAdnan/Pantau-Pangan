<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $cabai = Product::where('slug', 'cabai')->first();
        $beras = Product::where('slug', 'beras')->first();
        $ayam = Product::where('slug', 'daging-ayam')->first();

        // Cabai discussion
        $c1 = Comment::create([
            'user_id' => $users->where('role', 'user')->first()->id,
            'product_id' => $cabai->id,
            'body' => 'Kenapa harga cabai naik terus ya minggu ini? Ada yang tau penyebabnya?',
            'created_at' => now()->subDays(3),
        ]);

        Comment::create([
            'user_id' => $users->where('role', 'pedagang')->first()->id,
            'product_id' => $cabai->id,
            'parent_id' => $c1->id,
            'body' => 'Karena distribusi terhambat musim hujan. Stok dari Jawa Tengah berkurang drastis.',
            'created_at' => now()->subDays(3)->addHours(2),
        ]);

        Comment::create([
            'user_id' => $users->where('email', 'andi@user.id')->first()->id,
            'product_id' => $cabai->id,
            'parent_id' => $c1->id,
            'body' => 'Di pasar saya juga naik hampir 20%. Semoga segera turun.',
            'created_at' => now()->subDays(2),
        ]);

        // Beras discussion
        $c2 = Comment::create([
            'user_id' => $users->where('email', 'rina@user.id')->first()->id,
            'product_id' => $beras->id,
            'body' => 'Ada rekomendasi pasar yang jual beras murah di Jakarta?',
            'created_at' => now()->subDays(5),
        ]);

        Comment::create([
            'user_id' => $users->where('email', 'budi@pedagang.id')->first()->id,
            'product_id' => $beras->id,
            'parent_id' => $c2->id,
            'body' => 'Coba cek Pasar Kramat Jati, biasanya lebih murah karena dekat gudang distribusi.',
            'created_at' => now()->subDays(4),
        ]);

        // Ayam discussion
        Comment::create([
            'user_id' => $users->where('email', 'hendra@user.id')->first()->id,
            'product_id' => $ayam->id,
            'body' => 'Harga daging ayam stabil bulan ini. Bagus buat yang mau stok.',
            'created_at' => now()->subDays(1),
        ]);

        Comment::create([
            'user_id' => $users->where('email', 'maya@user.id')->first()->id,
            'product_id' => $cabai->id,
            'body' => 'Terima kasih info harga cabai dari para pedagang! Sangat membantu untuk belanja harian.',
            'likes_count' => 3,
            'created_at' => now()->subDays(1),
        ]);
    }
}
