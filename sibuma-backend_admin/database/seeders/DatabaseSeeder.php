<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin BPS Madiun',
            'email' => 'admin@bps.go.id',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        // Create sample responden for testing
        $user = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => Hash::make('password'),
            'role' => 'responden',
        ]);

        // Create sample usaha
        \App\Models\Usaha::create([
            'user_id' => $user->id,
            'nama_usaha' => 'Toko Berkah',
            'kategori' => 'perdagangan',
            'kode_kategori' => 'PDG',
        ]);

        $user2 = User::create([
            'name' => 'Siti Aminah',
            'email' => 'siti@example.com',
            'password' => Hash::make('password'),
            'role' => 'responden',
        ]);

        \App\Models\Usaha::create([
            'user_id' => $user2->id,
            'nama_usaha' => 'Konveksi Maju',
            'kategori' => 'manufaktur',
            'kode_kategori' => 'MNF',
        ]);

        $user3 = User::create([
            'name' => 'Agus Prasetyo',
            'email' => 'agus@example.com',
            'password' => Hash::make('password'),
            'role' => 'responden',
        ]);

        \App\Models\Usaha::create([
            'user_id' => $user3->id,
            'nama_usaha' => 'Jasa Service AC',
            'kategori' => 'jasa',
            'kode_kategori' => 'JSA',
        ]);
    }
}
