<?php

use App\Models\Entry;
use App\Models\Feed;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(RefreshDatabase::class);

it('can manage entries for a feed', function () {
    $user = User::factory()->create();
    $feed = Feed::factory()->create(['title' => 'Tech News']);
    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'name' => 'Initial Entry',
        'description' => 'A starter entry',
    ]);

    $this->actingAs($user);

    $page = visit('/feeds/'.$feed->id)
        ->assertSee('Initial Entry');

    // Create Entry
    $page->click('Add Entry')
        ->waitForText('New Entry')
        ->fill('name', 'Pest 4 Released')
        ->fill('description', 'The latest version is out')
        ->click('Save Entry')
        ->waitForText('Pest 4 Released');

    assertDatabaseHas('entries', ['name' => 'Pest 4 Released']);

    // Edit Entry
    $page->click('table tbody tr:first-child button.h-8.w-8') // Open dropdown for the first row
        ->waitForText('Edit')
        ->click('Edit')
        ->waitForText('Edit Entry')
        ->fill('edit-name', 'Pest 4.0 Released')
        ->click('Update Entry')
        ->waitForText('Pest 4.0 Released');

    assertDatabaseHas('entries', ['name' => 'Pest 4.0 Released']);

    // Delete Entry
    $page->script('window.confirm = function () { return true; }');
    $page->click('table tbody tr:first-child button.h-8.w-8')
        ->waitForText('Delete')
        ->click('Delete')
        ->wait(1)
        ->assertDontSee('Pest 4.0 Released');

    assertDatabaseMissing('entries', ['name' => 'Pest 4.0 Released']);
});
