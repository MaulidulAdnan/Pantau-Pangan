<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\MerchantProfile;
use App\Models\MerchantStore;
use App\Models\Market;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Admin PantauPangan',
            'email' => 'admin@pantaupangan.id',
            'password' => 'password123',
            'role' => 'admin',
            'phone' => '08123456789',
            'status' => 'active',
        ]);

        // Pedagang 1 (approved) - Jakarta region, 2 stores
        $pedagang1 = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@pedagang.id',
            'password' => 'password123',
            'role' => 'pedagang',
            'phone' => '08234567890',
            'status' => 'active',
        ]);
        MerchantProfile::create([
            'user_id' => $pedagang1->id,
            'store_name' => 'Toko Budi Jaya',
            'store_address' => 'Blok A No. 15',
            'market_id' => 1, // Pasar Tanah Abang (Jakarta)
            'description' => 'Pedagang sembako sejak 2010',
            'status' => 'approved',
            'verified_at' => now(),
            'total_contributions' => 45,
            'valid_contributions' => 43,
        ]);
        $market1 = Market::find(1);
        MerchantStore::create([
            'user_id' => $pedagang1->id,
            'region_id' => $market1->region_id,
            'market_id' => 1,
            'store_name' => 'Toko Budi Jaya',
            'store_address' => 'Blok A No. 15',
            'status' => 'approved',
        ]);
        MerchantStore::create([
            'user_id' => $pedagang1->id,
            'region_id' => $market1->region_id,
            'market_id' => 2, // Pasar Senen (same region: Jakarta)
            'store_name' => 'Toko Budi Senen',
            'store_address' => 'Lantai 1 No. 5',
            'status' => 'approved',
        ]);

        // Pedagang 2 (approved) - Bandung region
        $pedagang2 = User::create([
            'name' => 'Siti Rahayu',
            'email' => 'siti@pedagang.id',
            'password' => 'password123',
            'role' => 'pedagang',
            'phone' => '08345678901',
            'status' => 'active',
        ]);
        MerchantProfile::create([
            'user_id' => $pedagang2->id,
            'store_name' => 'Warung Siti',
            'store_address' => 'Blok C No. 8',
            'market_id' => 4, // Pasar Baru Bandung
            'description' => 'Pedagang sayur dan bumbu dapur',
            'status' => 'approved',
            'verified_at' => now(),
            'total_contributions' => 32,
            'valid_contributions' => 30,
        ]);
        $market4 = Market::find(4);
        MerchantStore::create([
            'user_id' => $pedagang2->id,
            'region_id' => $market4->region_id,
            'market_id' => 4,
            'store_name' => 'Warung Siti',
            'store_address' => 'Blok C No. 8',
            'status' => 'approved',
        ]);

        // Pedagang 3 (approved) - Surabaya region
        $pedagang3 = User::create([
            'name' => 'Agus Wijaya',
            'email' => 'agus@pedagang.id',
            'password' => 'password123',
            'role' => 'pedagang',
            'phone' => '08456789012',
            'status' => 'active',
        ]);
        MerchantProfile::create([
            'user_id' => $pedagang3->id,
            'store_name' => 'Toko Agus Makmur',
            'store_address' => 'Lantai 2 No. 22',
            'market_id' => 6, // Pasar Pabean (Surabaya)
            'description' => 'Pedagang daging dan ikan segar',
            'status' => 'approved',
            'verified_at' => now(),
            'total_contributions' => 28,
            'valid_contributions' => 27,
        ]);
        $market6 = Market::find(6);
        MerchantStore::create([
            'user_id' => $pedagang3->id,
            'region_id' => $market6->region_id,
            'market_id' => 6,
            'store_name' => 'Toko Agus Makmur',
            'store_address' => 'Lantai 2 No. 22',
            'status' => 'approved',
        ]);

        // Regular users
        $users = [
            ['name' => 'Dewi Lestari', 'email' => 'dewi@user.id'],
            ['name' => 'Andi Pratama', 'email' => 'andi@user.id'],
            ['name' => 'Rina Wulandari', 'email' => 'rina@user.id'],
            ['name' => 'Hendra Kusuma', 'email' => 'hendra@user.id'],
            ['name' => 'Maya Putri', 'email' => 'maya@user.id'],
        ];

        foreach ($users as $u) {
            User::create(array_merge($u, [
                'password' => 'password123',
                'role' => 'user',
                'status' => 'active',
            ]));
        }
    }
}
