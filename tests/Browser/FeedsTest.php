<?php

use App\Models\Feed;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(RefreshDatabase::class);

it('can manage feeds on the dashboard', function () {
    $user = User::factory()->create();
    $feed = Feed::factory()->create(['title' => 'Initial Feed']);

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
    $page->script('window.confirm = function () { return true; }');
    $page->click('table tbody tr:first-child button.h-8.w-8')
        ->waitForText('Delete')
        ->click('Delete')
        ->wait(1)
        ->assertDontSee('Updated Awesome Feed');

    assertDatabaseMissing('feeds', ['title' => 'Updated Awesome Feed']);
});
