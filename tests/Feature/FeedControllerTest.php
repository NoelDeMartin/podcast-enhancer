<?php

use App\Models\Feed;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('can update custom feed', function () {
    $feed = Feed::factory()->for($this->user)->create([
        'title' => 'Old Title',
        'description' => 'Old Description',
        'rss_url' => null,
    ]);

    $response = $this->actingAs($this->user)
        ->put(route('feeds.update', $feed), [
            'title' => 'New Title',
            'description' => 'New Description',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('feeds', [
        'id' => $feed->id,
        'title' => 'New Title',
        'description' => 'New Description',
    ]);
});

it('cannot update restricted fields for external feed', function () {
    Storage::fake('public');

    $feed = Feed::factory()->for($this->user)->create([
        'title' => 'Original Title',
        'description' => 'Original Description',
        'image_url' => 'https://example.com/original.png',
        'rss_url' => 'https://example.com/feed.xml',
    ]);

    $response = $this->actingAs($this->user)
        ->put(route('feeds.update', $feed), [
            'title' => 'Attempted New Title',
            'description' => 'Attempted New Description',
            'image_url' => 'https://example.com/new.png',
            'sync_frequency' => 3600,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('feeds', [
        'id' => $feed->id,
        'title' => 'Original Title',
        'description' => 'Original Description',
        'image_url' => 'https://example.com/original.png',
        'sync_frequency' => 3600,
    ]);
});

it('cannot update image file for external feed', function () {
    Storage::fake('public');

    $feed = Feed::factory()->for($this->user)->create([
        'title' => 'Original Title',
        'image_url' => 'https://example.com/original.png',
        'rss_url' => 'https://example.com/feed.xml',
    ]);

    $image = UploadedFile::fake()->image('new.jpg');

    $response = $this->actingAs($this->user)
        ->put(route('feeds.update', $feed), [
            'title' => 'Original Title',
            'image_file' => $image,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('feeds', [
        'id' => $feed->id,
        'image_url' => 'https://example.com/original.png',
    ]);

    Storage::disk('public')->assertMissing('images/'.$image->hashName());
});
