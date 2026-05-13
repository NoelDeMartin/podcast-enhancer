<?php

use App\Models\User;

use function Pest\Laravel\artisan;

it('can top up credits by user id using options', function () {
    $user = User::factory()->create(['credits' => 10]);

    artisan('credits:top-up', [
        '--user' => $user->id,
        '--credits' => 50,
        '--description' => 'Test top up',
    ])
        ->assertSuccessful()
        ->expectsOutputToContain('Successfully added 50 credits');

    $user->refresh();
    expect($user->credits)->toBe(60)
        ->and($user->creditTopUps)->toHaveCount(1)
        ->and($user->creditTopUps->first()->credits)->toBe(50)
        ->and($user->creditTopUps->first()->description)->toBe('Test top up');
});

it('can top up credits by email using options', function () {
    $user = User::factory()->create(['email' => 'test@example.com', 'credits' => 10]);

    artisan('credits:top-up', [
        '--user' => 'test@example.com',
        '--credits' => 20,
        '--description' => 'Email top up',
    ])
        ->assertSuccessful()
        ->expectsOutputToContain('Successfully added 20 credits');

    $user->refresh();
    expect($user->credits)->toBe(30);
});

it('prompts for missing information', function () {
    $user = User::factory()->create(['name' => 'Prompt User', 'email' => 'prompt@example.com', 'credits' => 10]);

    artisan('credits:top-up')
        ->expectsQuestion('Who should receive the credits?', $user->id)
        ->expectsQuestion('How many credits to add?', '100')
        ->expectsQuestion('What is the reason for this top-up?', 'Prompted reason')
        ->assertSuccessful()
        ->expectsOutputToContain('Successfully added 100 credits');

    $user->refresh();
    expect($user->credits)->toBe(110);
});

it('fails if user is not found', function () {
    artisan('credits:top-up', [
        '--user' => 'nonexistent@example.com',
        '--credits' => 10,
        '--description' => 'Test',
    ])
        ->assertFailed()
        ->expectsOutputToContain('User [nonexistent@example.com] not found');
});
