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
    $feed = Feed::factory()->for($user)->create(['title' => 'Tech News']);
    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'name' => 'Initial Entry',
    ]);

    $this->actingAs($user);

    $page = visit('/feeds/'.$feed->id)
        ->assertSee('Initial Entry');

    // Create Entry
    $page->click('Add Entry')
        ->waitForText('New Entry')
        ->fill('name', 'Pest 4 Released')
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
        ->click('Delete');

    $deleted = false;

    for ($attempt = 0; $attempt < 25; $attempt++) {
        if (! Entry::query()->where('name', 'Pest 4.0 Released')->exists()) {
            $deleted = true;
            break;
        }

        usleep(200_000);
    }

    expect($deleted)->toBeTrue();

    visit('/feeds/'.$feed->id)->assertDontSee('Pest 4.0 Released');

    assertDatabaseMissing('entries', ['name' => 'Pest 4.0 Released']);
});

it('can view an entry page', function () {
    $user = User::factory()->create();
    $feed = Feed::factory()->for($user)->create(['title' => 'Tech News']);
    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'name' => 'My Awesome Entry',
        'summary' => 'This is a great summary.',
        'original_summary' => 'This is the original description.',
        'transcription_path' => null,
    ]);

    $this->actingAs($user);

    visit('/feeds/'.$feed->id.'/entries/'.$entry->id)
        ->waitForText('My Awesome Entry')
        ->assertSee('AI Summary')
        ->assertSee('This is a great summary.')
        ->waitForText('Original Description')
        ->assertSee('This is the original description.')
        ->assertSee('Tech News');
});

it('can view entry details in a dialog on the feed page', function () {
    $user = User::factory()->create();
    $feed = Feed::factory()->for($user)->create(['title' => 'Tech News']);
    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'name' => 'Detailed Entry',
        'summary' => 'AI generated summary.',
        'original_summary' => 'This is the original description.',
    ]);

    $this->actingAs($user);

    visit('/feeds/'.$feed->id)
        ->waitForText('Detailed Entry')
        ->click('[data-test="view-details"]')
        ->waitForText('Summary')
        ->waitForText('Original Description')
        ->assertSee('This is the original description.');
});
