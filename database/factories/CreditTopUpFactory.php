<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CreditTopUpFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'credits' => $this->faker->numberBetween(10, 100),
            'description' => $this->faker->sentence(),
        ];
    }
}
