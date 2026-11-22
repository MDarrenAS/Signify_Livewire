<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Akun Admin
        User::updateOrCreate(
            ['email' => 'admin@signify.com'],
            [
                'name' => 'Admin Signify',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // Akun User Biasa
        User::updateOrCreate(
            ['email' => 'user@signify.com'],
            [
                'name' => 'Pengguna Signify',
                'password' => Hash::make('password'),
                'role' => 'user',
                'is_active' => true,
            ]
        );

        // Seed kurikulum modul
        $this->call(CurriculumSeeder::class);
    }
}
