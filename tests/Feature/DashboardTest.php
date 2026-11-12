<?php

use App\Models\Feed;
use App\Models\FeedJobBatch;
use App\Models\JobBatch;
use App\Models\User;
use Illuminate\Support\Str;

it('redirects guests to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

it('allows authenticated users to visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard/Index')
        ->where('appUrl', rtrim((string) url('/'), '/')));
});

it('redirects unverified users to the verification notice page', function () {
    $user = User::factory()->unverified()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('verification.notice'));
});

it('returns all feeds on the dashboard', function () {
    $user = User::factory()->create();
    Feed::factory()->count(15)->for($user)->create();

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard/Index')
        ->has('feeds', 15)
        ->missing('feeds.links')
    );
});

it('can filter feeds by title', function () {
    $user = User::factory()->create();
    Feed::factory()->for($user)->create(['title' => 'Laravel News']);
    Feed::factory()->for($user)->create(['title' => 'PHP Weekly']);
    Feed::factory()->for($user)->create(['title' => 'Tech Talks']);

    $response = $this->actingAs($user)
        ->get(route('dashboard').'?search=Laravel');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard/Index')
        ->has('feeds', 1)
        ->where('filters.search', 'Laravel')
    );

    $response = $this->actingAs($user)
        ->get(route('dashboard').'?search=Weekly');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard/Index')
        ->has('feeds', 1)
        ->where('filters.search', 'Weekly')
    );
});

it('includes latestJobBatch and can permissions for feeds on the dashboard', function () {
    $user = User::factory()->create();
    Feed::factory()->for($user)->create();

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard/Index')
        ->has('feeds.0', fn ($page) => $page
            ->has('latest_job_batch')
            ->has('can', fn ($page) => $page
                ->has('update')
                ->has('delete')
                ->has('sync')
            )
            ->etc()
        )
    );
});

it('loads latestJobBatch for feeds on the dashboard including failed status', function () {
    $user = User::factory()->create();
    $feed = Feed::factory()->for($user)->create([
        'rss_url' => 'https://example.com/feed.xml',
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

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard/Index')
        ->where('feeds.0.latest_job_batch.job_batch.id', $jobBatchId)
        ->where('feeds.0.latest_job_batch.job_batch.cancelled_at', fn ($val) => ! is_null($val))
    );
});
