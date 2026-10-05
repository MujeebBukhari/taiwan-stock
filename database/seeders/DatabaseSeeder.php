<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::updateOrCreate(
            ['email' => 'admin@stockportfolio.local'],
            [
                'name' => 'System Administrator',
                'password' => 'password123',
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@stockportfolio.local'],
            [
                'name' => 'Standard Investor',
                'password' => 'password123',
                'role' => User::ROLE_USER,
                'is_active' => true,
            ]
        );
    }
}
