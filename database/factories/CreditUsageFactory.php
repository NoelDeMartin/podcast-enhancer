<?php

namespace Database\Factories;

use App\Models\CreditUsage;
use App\Models\Entry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CreditUsage>
 */
class CreditUsageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'entry_id' => Entry::factory(),
            'credits' => $this->faker->numberBetween(1, 100),
        ];
    }
}
