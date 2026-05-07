<?php

use App\Models\Entry;
use App\Models\Feed;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows guests to view a feed page', function () {
    $user = User::factory()->create();
    $feed = Feed::factory()->for($user)->create(['title' => 'Public Feed']);
    Entry::factory()->for($feed)->create(['name' => 'Public Entry']);

    visit('/feeds/'.$feed->slug)
        ->waitForText('Public Feed')
        ->waitForText('Public Entry')
        ->assertNoJavaScriptErrors()
        ->assertDontSee('Add Entry')
        ->assertDontSee('Synchronize');
});

it('allows guests to view an entry page', function () {
    $user = User::factory()->create();
    $feed = Feed::factory()->for($user)->create(['title' => 'Public Feed']);
    $entry = Entry::factory()->for($feed)->create([
        'name' => 'Public Entry',
        'summary' => 'Public Summary',
    ]);

    visit('/feeds/'.$feed->slug.'/entries/'.$entry->slug)
        ->waitForText('Public Entry')
        ->assertSee('Public Summary')
        ->assertNoJavaScriptErrors();
});

it('does not show management actions to guests on the feed page', function () {
    $user = User::factory()->create();
    $feed = Feed::factory()->for($user)->create(['title' => 'Public Feed']);
    Entry::factory()->for($feed)->create(['name' => 'Public Entry']);

    visit('/feeds/'.$feed->slug)
        ->waitForText('Public Entry')
        ->assertDontSee('Add Entry')
        ->assertDontSee('Synchronize')
        // The dropdown trigger button should not be present if can.update and can.delete are false
        ->assertNotPresent('Open menu for Public Feed');
});
