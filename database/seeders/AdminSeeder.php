<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::firstOrCreate(
            ['email' => 'admin@pta.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'phone' => '9999999999',
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
