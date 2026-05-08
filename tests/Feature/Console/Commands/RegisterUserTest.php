<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

it('can register a new user', function () {
    Notification::fake();

    $this->artisan('auth:register')
        ->expectsQuestion('What is the user\'s name?', 'Test User')
        ->expectsQuestion('What is the user\'s email address?', 'test@example.com')
        ->expectsQuestion('What is the user\'s password?', 'password123')
        ->expectsQuestion('Confirm the password', 'password123')
        ->assertExitCode(Command::SUCCESS);

    $this->assertDatabaseHas('users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    $user = User::where('email', 'test@example.com')->first();
    Notification::assertSentTo($user, VerifyEmail::class);
});

it('shows validation errors when registration fails', function () {
    $this->artisan('auth:register')
        ->expectsQuestion('What is the user\'s name?', 'Test User')
        ->expectsQuestion('What is the user\'s email address?', 'not-an-email')
        ->expectsQuestion('What is the user\'s password?', 'password123')
        ->expectsQuestion('Confirm the password', 'password123')
        ->assertExitCode(Command::FAILURE);

    $this->assertDatabaseMissing('users', [
        'name' => 'Test User',
    ]);
});

it('can register a new user using options', function () {
    $this->artisan('auth:register', [
        '--name' => 'Option User',
        '--email' => 'option@example.com',
        '--password' => 'password123',
        '--password_confirmation' => 'password123',
    ])->assertExitCode(Command::SUCCESS);

    $this->assertDatabaseHas('users', [
        'name' => 'Option User',
        'email' => 'option@example.com',
    ]);
});

it('can register a new user using a mix of options and prompts', function () {
    $this->artisan('auth:register', [
        '--name' => 'Mix User',
        '--email' => 'mix@example.com',
    ])
        ->expectsQuestion('What is the user\'s password?', 'password123')
        ->expectsQuestion('Confirm the password', 'password123')
        ->assertExitCode(Command::SUCCESS);

    $this->assertDatabaseHas('users', [
        'name' => 'Mix User',
        'email' => 'mix@example.com',
    ]);
});
