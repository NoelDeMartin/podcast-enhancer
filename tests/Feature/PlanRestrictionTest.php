<?php

use App\Models\Entry;
use App\Models\Feed;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

it('allows pro users to upload audio files to entries', function () {
    Storage::fake('public');
    Bus::fake();
    $user = User::factory()->pro()->create();
    $feed = Feed::factory()->for($user)->create();
    $file = UploadedFile::fake()->create('audio.mp3', 1024);

    $this->actingAs($user)
        ->post(route('entries.store', $feed), [
            'feed_id' => $feed->id,
            'name' => 'Pro Entry',
            'published_at' => now()->format('Y-m-d\TH:i'),
            'file' => $file,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('entries', ['name' => 'Pro Entry']);
});

it('denies basic users from uploading audio files to entries', function () {
    Storage::fake('public');
    Bus::fake();
    $user = User::factory()->basic()->create();
    $feed = Feed::factory()->for($user)->create();
    $file = UploadedFile::fake()->create('audio.mp3', 1024);

    $this->actingAs($user)
        ->post(route('entries.store', $feed), [
            'feed_id' => $feed->id,
            'name' => 'Basic Entry',
            'published_at' => now()->format('Y-m-d\TH:i'),
            'file' => $file,
        ])
        ->assertStatus(403);

    $this->assertDatabaseMissing('entries', ['name' => 'Basic Entry']);
});

it('allows pro users to upload images to feeds', function () {
    Storage::fake('public');
    $user = User::factory()->pro()->create();
    $image = UploadedFile::fake()->image('feed.jpg');

    $this->actingAs($user)
        ->post(route('feeds.store'), [
            'title' => 'Pro Feed',
            'image_file' => $image,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('feeds', ['title' => 'Pro Feed']);
});

it('denies basic users from uploading images to feeds', function () {
    Storage::fake('public');
    $user = User::factory()->basic()->create();
    $image = UploadedFile::fake()->image('feed.jpg');

    $this->actingAs($user)
        ->post(route('feeds.store'), [
            'title' => 'Basic Feed',
            'image_file' => $image,
        ])
        ->assertStatus(403);

    $this->assertDatabaseMissing('feeds', ['title' => 'Basic Feed']);
});

it('allows basic users to generate enhancements for the first time', function () {
    Bus::fake();
    $user = User::factory()->basic()->create();
    $feed = Feed::factory()->for($user)->create();
    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'audio_url' => 'https://example.com/audio.mp3',
        'transcription_path' => null,
    ]);

    $this->actingAs($user)
        ->post(route('entries.produce', [$feed, $entry]))
        ->assertRedirect();
});

it('denies basic users from regenerating enhancements if transcription already exists', function () {
    Bus::fake();
    $user = User::factory()->basic()->create();
    $feed = Feed::factory()->for($user)->create();
    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'audio_url' => 'https://example.com/audio.mp3',
        'transcription_path' => 'transcriptions/exists.json',
    ]);

    $this->actingAs($user)
        ->post(route('entries.produce', [$feed, $entry]))
        ->assertStatus(403);
});

it('allows pro users to regenerate enhancements even if transcription already exists', function () {
    Bus::fake();
    $user = User::factory()->pro()->create();
    $feed = Feed::factory()->for($user)->create();
    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'audio_url' => 'https://example.com/audio.mp3',
        'transcription_path' => 'transcriptions/exists.json',
    ]);

    $this->actingAs($user)
        ->post(route('entries.produce', [$feed, $entry]))
        ->assertRedirect();
});

it('denies any user from using produce on an already processed entry', function () {
    Bus::fake();
    $user = User::factory()->pro()->create();
    $feed = Feed::factory()->for($user)->create();
    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'audio_url' => 'https://example.com/audio.mp3',
        'transcription_path' => 'transcriptions/exists.json',
    ]);

    // Although they are PRO, 'produce' (first-time) is forbidden.
    // They must use 'regenerate'. The controller handles this split.
    // This test ensures the Policy logic is correct.
    expect($user->can('produce', $entry))->toBeFalse();
    expect($user->can('regenerate', $entry))->toBeTrue();
});
