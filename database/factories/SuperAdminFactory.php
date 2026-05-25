<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class SuperAdminFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'   => $this->faker->name(),
            'email'  => $this->faker->unique()->safeEmail(),
            'phone'  => $this->faker->unique()->numerify('##########'),
            'password' => Hash::make('password123'), // default password
            'amount' => $this->faker->randomFloat(2, 1000, 100000),
            'userid' => strtoupper(Str::random(10)),
        ];
    }
}
