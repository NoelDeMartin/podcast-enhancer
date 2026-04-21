<?php

use App\Models\Entry;
use App\Models\Feed;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(RefreshDatabase::class);

it('can manage feeds on the dashboard', function () {
    $user = User::factory()->create();
    $feed = Feed::factory()->for($user)->create(['title' => 'Initial Feed']);

    $this->actingAs($user);

    $page = visit('/dashboard')
        ->assertSee('Initial Feed');

    // Create Feed
    $page->click('New Feed')
        ->waitForText('Create New Feed')
        ->fill('title', 'My Awesome Feed')
        ->click('Create Feed')
        ->waitForText('My Awesome Feed');

    assertDatabaseHas('feeds', ['title' => 'My Awesome Feed']);

    // Edit Feed
    $page->click('table tbody tr:first-child button.h-8.w-8') // The dropdown trigger for the first row
        ->waitForText('Edit')
        ->click('Edit')
        ->waitForText('Edit Feed')
        ->fill('edit-title', 'Updated Awesome Feed')
        ->click('Update Feed')
        ->waitForText('Updated Awesome Feed');

    assertDatabaseHas('feeds', ['title' => 'Updated Awesome Feed']);

    // Delete Feed
    $feedToDelete = Feed::query()
        ->where('title', 'Updated Awesome Feed')
        ->firstOrFail();

    $this->delete(route('feeds.destroy', $feedToDelete));

    retry(25, function () {
        visit('/dashboard')->assertDontSee('Updated Awesome Feed');
    }, 200);

    retry(25, function () {
        assertDatabaseMissing('feeds', ['title' => 'Updated Awesome Feed']);
    }, 200);

    assertDatabaseMissing('feeds', ['title' => 'Updated Awesome Feed']);
});

it('can access public rss feed without authentication', function () {
    $user = User::factory()->create();
    $feed = Feed::factory()->for($user)->create([
        'title' => 'Security Test Feed',
        'description' => 'Security Test Description',
    ]);

    Entry::factory()->for($feed)->create([
        'name' => 'Secure Episode',
        'summary' => 'This is a summary that triggers a relation check.',
        'audio_url' => null,
        'chapters' => [
            ['title' => 'Intro', 'startTime' => 0],
        ],
    ]);

    visit(route('feeds.rss', $feed))
        ->assertSee('Security Test Feed')
        ->assertSee('Secure Episode')
        ->assertSee('Timestamps');
});
