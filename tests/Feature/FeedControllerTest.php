<?php

use App\Models\Entry;
use App\Models\Feed;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('paginates entries on show', function () {
    $feed = Feed::factory()->for($this->user)->create();
    Entry::factory()->count(15)->for($feed)->create();

    $response = $this->actingAs($this->user)
        ->get(route('feeds.show', $feed));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Feeds/Show')
        ->has('entries.data', 10)
        ->has('entries.links')
    );
});

it('can filter entries by name', function () {
    $feed = Feed::factory()->for($this->user)->create();
    Entry::factory()->for($feed)->create(['name' => 'First Episode']);
    Entry::factory()->for($feed)->create(['name' => 'Second Episode']);
    Entry::factory()->for($feed)->create(['name' => 'Special Content']);

    $response = $this->actingAs($this->user)
        ->get(route('feeds.show', $feed).'?search=Episode');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Feeds/Show')
        ->has('entries.data', 2)
        ->where('filters.search', 'Episode')
    );

    $response = $this->actingAs($this->user)
        ->get(route('feeds.show', $feed).'?search=Special');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Feeds/Show')
        ->has('entries.data', 1)
        ->where('filters.search', 'Special')
    );
});

it('allows pro users to create manual feeds', function () {
    $user = User::factory()->create(['plan' => 'pro']);

    $response = $this->actingAs($user)
        ->post(route('feeds.store'), [
            'title' => 'Manual Podcast',
            'description' => 'A manual podcast creation',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('feeds', [
        'user_id' => $user->id,
        'title' => 'Manual Podcast',
        'rss_url' => null,
    ]);
});

it('prevents basic users from creating manual feeds', function () {
    $user = User::factory()->create(['plan' => 'basic']);

    $response = $this->actingAs($user)
        ->post(route('feeds.store'), [
            'title' => 'Manual Podcast',
            'description' => 'A manual podcast creation',
        ]);

    $response->assertStatus(403);
    $this->assertDatabaseMissing('feeds', [
        'user_id' => $user->id,
        'title' => 'Manual Podcast',
    ]);
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
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('feeds', [
        'id' => $feed->id,
        'title' => 'Original Title',
        'description' => 'Original Description',
        'image_url' => 'https://example.com/original.png',
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
