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
        ->component('Dashboard')
        ->has('feeds.data', 10)
        ->has('feeds.links')
    );
});
