<?php

use App\Models\CreditUsage;
use App\Models\Entry;
use App\Models\Feed;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can open the credits modal and see usage history', function () {
    $user = User::factory()->create(['credits' => 100]);
    $feed = Feed::factory()->for($user)->create();
    $entry = Entry::factory()->for($feed)->create(['name' => 'Episode 1']);

    CreditUsage::factory()->create([
        'user_id' => $user->id,
        'entry_id' => $entry->id,
        'credits' => 10,
    ]);

    $this->actingAs($user);

    visit('/dashboard')
        ->waitForText('100')
        ->click('text=/Credits:.*100/')
        ->waitForText('Your current balance')
        ->waitForText('Episode 1')
        ->assertSee('Your current balance')
        ->assertSee('-10');
});
