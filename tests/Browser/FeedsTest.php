<?php

use App\Models\Entry;
use App\Models\Feed;
use App\Models\FeedJobBatch;
use App\Models\JobBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(RefreshDatabase::class);

it('can manage feeds on the dashboard', function () {
    $user = User::factory()->state(['plan' => 'pro'])->create();
    $feed = Feed::factory()->for($user)->create(['title' => 'Initial Feed']);

    $page = $this->actingAs($user)
        ->visit('/dashboard')
        ->waitForText('Initial Feed');

    // Create Feed
    $page->click('New Podcast')
        ->waitForText('Add New Podcast')
        ->click('Manual Creation')
        ->fill('title', 'My Awesome Feed')
        ->click('Create Podcast')
        ->waitForText('My Awesome Feed')
        ->wait(1.0);

    assertDatabaseHas('feeds', ['title' => 'My Awesome Feed']);

    // Edit Feed
    $page->press('Open menu for My Awesome Feed')
        ->wait(0.5)
        ->click('Edit')
        ->waitForText('Edit Podcast')
        ->fill('edit-title', 'Updated Awesome Feed')
        ->click('Update Podcast')
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

it('shows a red dot on the dashboard when a feed sync has failed', function () {
    $user = User::factory()->create();
    $feed = Feed::factory()->for($user)->create([
        'title' => 'Failing Sync Feed',
        'rss_url' => 'https://example.com/feed.xml',
        'last_synced_at' => now()->subDay(),
    ]);

    $jobBatchId = (string) Str::uuid();

    JobBatch::forceCreate([
        'id' => $jobBatchId,
        'name' => 'Sync feed '.$feed->id,
        'total_jobs' => 1,
        'pending_jobs' => 0,
        'failed_jobs' => 1,
        'failed_job_ids' => [(string) Str::uuid()],
        'cancelled_at' => now()->timestamp,
        'created_at' => now()->timestamp,
    ]);

    FeedJobBatch::create([
        'feed_id' => $feed->id,
        'batch_id' => $jobBatchId,
    ]);

    $this->actingAs($user)
        ->visit('/dashboard')
        ->waitForText('Failing Sync Feed')
        ->assertPresent('.bg-red-500');
});
