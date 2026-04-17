<?php

use App\Models\Entry;
use App\Models\Feed;
use Illuminate\Support\Facades\Storage;

it('generates an rss feed for a feed', function () {
    Storage::fake('public');

    $feed = Feed::factory()->create(['title' => 'My Podcast']);
    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'name' => 'Episode 1',
        'summary' => 'This is the first episode summary.',
        'audio_url' => 'entries/audio.mp3',
    ]);

    Storage::disk('public')->put('entries/audio.mp3', 'dummy content');

    $response = $this->get(route('feeds.rss', $feed));

    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'text/xml; charset=UTF-8');

    $response->assertSee('<title>My Podcast</title>', false);
    $response->assertSee('<title>Episode 1</title>', false);
    $response->assertSee('<description><![CDATA[<p>This is the first episode summary.</p>', false);
    $response->assertSee('Read episode transcription</a>', false);
    $response->assertSee('Episode enhanced by <a href="'.url('/').'">Podcasts Enhancer</a>', false);
    $response->assertSee('<pubDate>'.$entry->published_at->toRfc2822String().'</pubDate>', false);
    $response->assertSee(asset(Storage::disk('public')->url($entry->audio_url)), false);
    $response->assertSee('length="13"', false); // "dummy content" is 13 bytes
});

it('includes podcast chapters in rss when available', function () {
    Storage::fake('public');

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

    Storage::disk('public')->put('entries/audio.mp3', 'dummy content');

    $response = $this->get(route('feeds.rss', $feed));

    $response->assertSuccessful();
    $response->assertSee('xmlns:psc="http://podlove.org/simple-chapters"', false);
    $response->assertSee('<li>00:00 - Intro</li>', false);
    $response->assertSee('<li>01:00 - Main Topic</li>', false);
    $response->assertSee('<content:encoded><![CDATA[', false);
    $response->assertSee('<psc:chapters version="1.2">', false);
    $response->assertSee('<psc:chapter start="00:00:00" title="Intro" />', false);
    $response->assertSee('<psc:chapter start="00:01:00" title="Main Topic" />', false);
});

it('omits podcast chapters in rss when not available', function () {
    Storage::fake('public');

    $feed = Feed::factory()->create(['title' => 'My Podcast']);
    Entry::factory()->create([
        'feed_id' => $feed->id,
        'name' => 'Episode Without Chapters',
        'audio_url' => 'entries/audio.mp3',
        'chapters' => null,
    ]);

    Storage::disk('public')->put('entries/audio.mp3', 'dummy content');

    $response = $this->get(route('feeds.rss', $feed));

    $response->assertSuccessful();
    $response->assertDontSee('<psc:chapters', false);
});
