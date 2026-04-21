<?php

use App\Models\Entry;
use App\Models\Feed;
use App\Models\User;

it('deletes a feed and its entries', function () {
    $user = User::factory()->create();

    $feed = Feed::factory()->for($user)->create();
    $entries = Entry::factory()->count(3)->create([
        'feed_id' => $feed->id,
    ]);

    $response = $this->actingAs($user)->delete(route('feeds.destroy', $feed));

    $response->assertRedirect(route('dashboard'));

    $this->assertDatabaseMissing('feeds', ['id' => $feed->id]);

    foreach ($entries as $entry) {
        $this->assertDatabaseMissing('entries', ['id' => $entry->id]);
    }
});
