<?php

use App\Models\Entry;
use App\Models\Feed;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('has many entries', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $feed = Feed::factory()->create(['user_id' => $user->id]);
    $entry = Entry::factory()->create(['feed_id' => $feed->id]);

    expect($feed->entries)->toHaveCount(1);
    expect($feed->entries->first()->id)->toBe($entry->id);
    expect($entry->feed->id)->toBe($feed->id);
});
