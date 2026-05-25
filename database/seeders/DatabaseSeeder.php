<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SuperAdmin;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
{
    //$this->call(UserSeeder::class);
    // SuperAdmin::factory()->count(10)->create();
     $this->call(SuperAdminSeeder::class);
}

}
