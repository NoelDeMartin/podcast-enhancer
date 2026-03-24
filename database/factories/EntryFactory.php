<?php

namespace Database\Factories;

use App\Models\Entry;
use App\Models\Feed;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Entry>
 */
class EntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'feed_id' => Feed::factory(),
            'name' => fake()->sentence(),
            'description' => fake()->paragraph(),
        ];
    }
}
