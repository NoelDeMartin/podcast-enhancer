<?php

use App\Models\Entry;
use App\Models\Feed;
use Illuminate\Support\Facades\Storage;

it('generates an rss feed for a feed', function () {
    Storage::fake('local');

    $feed = Feed::factory()->create(['title' => 'My Podcast']);
    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'name' => 'Episode 1',
        'description' => 'This is the first episode.',
        'file_path' => 'entries/audio.mp3',
    ]);

    Storage::put('entries/audio.mp3', 'dummy content');

    $response = $this->get(route('feeds.rss', $feed));

    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'text/xml; charset=UTF-8');

    $response->assertSee('<title>My Podcast</title>', false);
    $response->assertSee('<title>Episode 1</title>', false);
    $response->assertSee('<description>This is the first episode.</description>', false);
    $response->assertSee(route('entries.file', $entry), false);
    $response->assertSee('length="13"', false); // "dummy content" is 13 bytes
});
