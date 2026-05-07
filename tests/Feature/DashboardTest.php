<?php

use App\Models\Feed;
use App\Models\User;

it('redirects guests to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

it('allows authenticated users to visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

it('paginates feeds on the dashboard', function () {
    $user = User::factory()->create();
    Feed::factory()->count(15)->for($user)->create();

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard/Index')
        ->has('feeds.data', 10)
        ->has('feeds.links')
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
        ->has('feeds.data', 1)
        ->where('filters.search', 'Laravel')
    );

    $response = $this->actingAs($user)
        ->get(route('dashboard').'?search=Weekly');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard/Index')
        ->has('feeds.data', 1)
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
        ->has('feeds.data.0', fn ($page) => $page
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
