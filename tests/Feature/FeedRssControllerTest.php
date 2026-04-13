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
        'summary' => 'This is the first episode summary.',
        'audio_url' => 'entries/audio.mp3',
    ]);

    Storage::put('entries/audio.mp3', 'dummy content');

    $response = $this->get(route('feeds.rss', $feed));

    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'text/xml; charset=UTF-8');

    $response->assertSee('<title>My Podcast</title>', false);
    $response->assertSee('<title>Episode 1</title>', false);
    $response->assertSee('<description><![CDATA[This is the first episode summary.]]></description>', false);
    $response->assertSee('<content:encoded><![CDATA[This is the first episode summary.]]></content:encoded>', false);
    $response->assertSee('<pubDate>'.$entry->published_at->toRfc2822String().'</pubDate>', false);
    $response->assertSee(route('entries.file', $entry), false);
    $response->assertSee('length="13"', false); // "dummy content" is 13 bytes
});

it('includes podcast chapters in rss when available', function () {
    Storage::fake('local');

    $feed = Feed::factory()->create(['title' => 'My Podcast']);
    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'name' => 'Episode With Chapters',
        'audio_url' => 'entries/audio.mp3',
        'chapters' => [
            ['title' => 'Intro', 'startTime' => 0],
            ['title' => 'Main Topic', 'startTime' => 60],
        ],
    ]);

    Storage::put('entries/audio.mp3', 'dummy content');

    $response = $this->get(route('feeds.rss', $feed));

    $response->assertSuccessful();
    $response->assertSee('<li>00:00 - Intro</li>', false);
    $response->assertSee('<li>01:00 - Main Topic</li>', false);
    $response->assertSee('<content:encoded><![CDATA[', false);
});

it('omits podcast chapters in rss when not available', function () {
    Storage::fake('local');

    $feed = Feed::factory()->create(['title' => 'My Podcast']);
    Entry::factory()->create([
        'feed_id' => $feed->id,
        'name' => 'Episode Without Chapters',
        'audio_url' => 'entries/audio.mp3',
        'chapters' => null,
    ]);

    Storage::put('entries/audio.mp3', 'dummy content');

    $response = $this->get(route('feeds.rss', $feed));

    $response->assertSuccessful();
    $response->assertDontSee('<podcast:chapters', false);
});
