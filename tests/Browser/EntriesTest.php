<?php

use App\Models\Entry;
use App\Models\Feed;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(RefreshDatabase::class);

it('can manage entries for a feed', function () {
    $user = User::factory()->pro()->create();
    $feed = Feed::factory()->for($user)->create(['title' => 'Tech News']);
    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'name' => 'Initial Entry',
    ]);

    $this->actingAs($user);

    $page = visit('/feeds/'.$feed->slug)
        ->assertSee('Initial Entry');

    // Create Entry
    $page->click('Add Episode')
        ->waitForText('New Episode')
        ->fill('name', 'Pest 4 Released')
        ->click('Save Episode')
        ->waitForText('Pest 4 Released');

    assertDatabaseHas('entries', ['name' => 'Pest 4 Released']);

    // Edit Entry
    $page->press('Open menu for Initial Entry')
        ->wait(0.5)
        ->waitForText('Edit')
        ->click('Edit')
        ->waitForText('Edit Episode')
        ->fill('edit-name', 'Pest 4.0 Released')
        ->click('Update Episode')
        ->waitForText('Pest 4.0 Released');

    assertDatabaseHas('entries', ['name' => 'Pest 4.0 Released']);

    // Delete Entry
    $page->script('window.confirm = function () { return true; }');
    $page->press('Open menu for Pest 4.0 Released')
        ->wait(0.5)
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

    visit('/feeds/'.$feed->slug)->assertDontSee('Pest 4.0 Released');

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

    visit('/feeds/'.$feed->slug.'/entries/'.$entry->slug)
        ->waitForText('My Awesome Entry')
        ->assertSee('AI Summary')
        ->assertSee('This is a great summary.')
        ->waitForText('Original Description')
        ->assertSee('This is the original description.')
        ->assertSee('Tech News');
});

it('can navigate to entry details from the feed page', function () {
    $user = User::factory()->create();
    $feed = Feed::factory()->for($user)->create(['title' => 'Tech News']);
    $entry = Entry::factory()->create([
        'feed_id' => $feed->id,
        'name' => 'Detailed Entry',
        'summary' => 'AI generated summary.',
        'original_summary' => 'This is the original description.',
    ]);

    $this->actingAs($user);

    visit('/feeds/'.$feed->slug)
        ->waitForText('Detailed Entry')
        ->click('Detailed Entry')
        ->waitForText('AI Summary')
        ->assertSee('AI generated summary.')
        ->waitForText('Original Description')
        ->assertSee('This is the original description.');
});
