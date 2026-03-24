<?php

use App\Models\Entry;
use App\Models\Feed;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('has many entries', function () {
    $feed = Feed::factory()->create();
    $entry = Entry::factory()->create(['feed_id' => $feed->id]);

    expect($feed->entries)->toHaveCount(1);
    expect($feed->entries->first()->id)->toBe($entry->id);
    expect($entry->feed->id)->toBe($feed->id);
});
