<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin User
        User::updateOrCreate(
            ['email' => 'admin@codegraphi.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('Admin@123'),
            ]
        );

        // Create Demo Users
        User::factory()->count(5)->create();
    }
}
