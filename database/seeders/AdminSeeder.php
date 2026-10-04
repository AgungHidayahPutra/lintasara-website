<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            [
                'email' => env('ADMIN_EMAIL', 'admin@lintasara.test'),
            ],
            [
                'name' => env('ADMIN_NAME', 'Administrator'),
                'password' => Hash::make(
                    env('ADMIN_PASSWORD', 'password')
                ),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
