<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('can render the confirm password screen', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('password.confirm'));

    $response->assertOk();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('auth/ConfirmPassword'),
    );
});

it('requires authentication for password confirmation', function () {
    $response = $this->get(route('password.confirm'));

    $response->assertRedirect(route('login'));
});
