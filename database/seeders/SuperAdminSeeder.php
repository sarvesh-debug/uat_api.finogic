<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\SuperAdmin;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        SuperAdmin::create([
            'name'   => 'Sarvesh Admin',
            'email'  => 'admin@codegraphi.com',
            'phone'  => '9876543210',
            'password' => Hash::make('admin123'), // 🔒 always hash passwords
            'amount' => 50000.00,
            'userid' => 'SUPERADMIN01',
        ]);

        // 👉 You can add more entries
        SuperAdmin::create([
            'name'   => 'Second Admin',
            'email'  => 'second@codegraphi.com',
            'phone'  => '9123456789',
            'password' => Hash::make('secret456'),
            'amount' => 25000.00,
            'userid' => 'SUPERADMIN02',
        ]);
    }
}
