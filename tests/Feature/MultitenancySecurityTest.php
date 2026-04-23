<?php

use App\Models\Entry;
use App\Models\Feed;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
});

it('can view another user\'s feed', function () {
    $feed = Feed::factory()->for($this->otherUser)->create();

    $this->actingAs($this->user)
        ->get(route('feeds.show', $feed))
        ->assertOk();
});

it('can view another user\'s entry', function () {
    $feed = Feed::factory()->for($this->otherUser)->create();
    $entry = Entry::factory()->create(['feed_id' => $feed->id]);

    $this->actingAs($this->user)
        ->get(route('entries.show', [$feed, $entry]))
        ->assertOk();
});

it('cannot update another user\'s entry', function () {
    $feed = Feed::factory()->for($this->otherUser)->create();
    $entry = Entry::factory()->create(['feed_id' => $feed->id]);

    $this->actingAs($this->user)
        ->put(route('entries.update', [$feed, $entry]), [
            'name' => 'Hacked',
        ])
        ->assertNotFound();
});

it('cannot delete another user\'s entry', function () {
    $feed = Feed::factory()->for($this->otherUser)->create();
    $entry = Entry::factory()->create(['feed_id' => $feed->id]);

    $this->actingAs($this->user)
        ->delete(route('entries.destroy', [$feed, $entry]))
        ->assertNotFound();
});

it('cannot produce another user\'s entry', function () {
    $feed = Feed::factory()->for($this->otherUser)->create();
    $entry = Entry::factory()->create(['feed_id' => $feed->id]);

    $this->actingAs($this->user)
        ->post(route('entries.produce', [$feed, $entry]))
        ->assertNotFound();
});

it('cannot access an entry even if providing own feed but other\'s entry ID', function () {
    $myFeed = Feed::factory()->for($this->user)->create();
    $otherFeed = Feed::factory()->for($this->otherUser)->create();
    $otherEntry = Entry::factory()->create(['feed_id' => $otherFeed->id]);

    // Scoped bindings should catch that $otherEntry does not belong to $myFeed
    $this->actingAs($this->user)
        ->get(route('entries.show', [$myFeed, $otherEntry]))
        ->assertNotFound();
});

it('cannot produce an entry even if providing own feed but other\'s entry ID', function () {
    $myFeed = Feed::factory()->for($this->user)->create();
    $otherFeed = Feed::factory()->for($this->otherUser)->create();
    $otherEntry = Entry::factory()->create(['feed_id' => $otherFeed->id]);

    $this->actingAs($this->user)
        ->post(route('entries.produce', [$myFeed, $otherEntry]))
        ->assertNotFound();
});

it('can view another user\'s RSS feed even when authenticated', function () {
    $feed = Feed::factory()->for($this->otherUser)->create();

    $this->actingAs($this->user)
        ->get(route('feeds.rss', $feed))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/xml; charset=utf-8');
});
