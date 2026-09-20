<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Production never gets the well-known dev password: a random one is
        // generated and printed once. Dev/test keep 'password' for workflows.
        $isProduction = app()->environment('production');
        $plainPassword = $isProduction ? Str::random(20) : 'password';

        $admin = Admin::firstOrCreate(
            ['email' => 'admin@pta.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make($plainPassword),
                'phone' => '9999999999',
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        if ($isProduction && $admin->wasRecentlyCreated) {
            $this->command?->warn('Seeded Super Admin admin@pta.com with random password: ' . $plainPassword);
            $this->command?->warn('Log in immediately and change it via Staff management.');
        }
    }
}
