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
        $name = fake()->sentence();

        return [
            'feed_id' => Feed::factory(),
            'name' => $name,
            'slug' => Entry::generateUniqueSlug($name),
            'published_at' => now(),
        ];
    }
}
