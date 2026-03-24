<?php

use Illuminate\Console\Command;

it('can register a new user', function () {
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
