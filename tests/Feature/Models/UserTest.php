<?php

use App\Models\Feed;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows all users to import feeds via RSS', function () {
    $user = User::factory()->create(['plan' => 'basic']);

    $this->assertTrue($user->can('create', Feed::class));
});
