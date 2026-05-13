<?php

use App\Models\CreditTopUp;
use App\Models\CreditUsage;
use App\Models\Entry;
use App\Models\EntryJobBatch;
use App\Models\FailedJob;
use App\Models\Feed;
use App\Models\JobBatch;
use App\Models\User;
use Illuminate\Support\Str;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

it('guests cannot see credit usage', function () {
    getJson(route('credits'))
        ->assertStatus(401);
});

it('authenticated users can see their credit usage and top-ups', function () {
    $user = User::factory()->create(['credits' => 140]);
    $feed = Feed::factory()->for($user)->create();
    $entry = Entry::factory()->for($feed)->create();

    CreditTopUp::factory()->create([
        'user_id' => $user->id,
        'credits' => 50,
        'description' => 'Welcome bonus',
        'created_at' => now()->subDays(2),
    ]);

    CreditUsage::factory()->create([
        'user_id' => $user->id,
        'entry_id' => $entry->id,
        'credits' => 10,
        'created_at' => now()->subDays(1),
    ]);

    actingAs($user)
        ->getJson(route('credits'))
        ->assertStatus(200)
        ->assertJsonPath('usages.data', fn ($usages) => count($usages) === 2)
        ->assertJsonPath('usages.data.0.type', 'usage')
        ->assertJsonPath('usages.data.0.credits', 10)
        ->assertJsonPath('usages.data.0.entry.name', $entry->name)
        ->assertJsonPath('usages.data.1.type', 'topup')
        ->assertJsonPath('usages.data.1.credits', 50)
        ->assertJsonPath('usages.data.1.description', 'Welcome bonus')
        ->assertJsonPath('current_credits', 140);
});

it('users cannot see other users credit usage or top-ups', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    CreditUsage::factory()->create([
        'user_id' => $otherUser->id,
        'credits' => 10,
    ]);

    CreditTopUp::factory()->create([
        'user_id' => $otherUser->id,
        'credits' => 50,
    ]);

    actingAs($user)
        ->getJson(route('credits'))
        ->assertStatus(200)
        ->assertJsonPath('usages.data', []);
});

it('credit usage response includes job status data', function () {
    $user = User::factory()->create(['credits' => 50]);
    $feed = Feed::factory()->for($user)->create();
    $entry = Entry::factory()->for($feed)->create();

    $jobBatchId = (string) Str::uuid();
    $failedJobUuid = (string) Str::uuid();

    JobBatch::forceCreate([
        'id' => $jobBatchId,
        'name' => 'Test Batch',
        'total_jobs' => 1,
        'pending_jobs' => 0,
        'failed_jobs' => 1,
        'failed_job_ids' => [$failedJobUuid],
        'cancelled_at' => now()->timestamp,
        'created_at' => now()->timestamp,
    ]);

    FailedJob::forceCreate([
        'uuid' => $failedJobUuid,
        'connection' => 'database',
        'queue' => 'default',
        'payload' => '{}',
        'exception' => 'Test Exception',
        'failed_at' => now(),
    ]);

    EntryJobBatch::create([
        'entry_id' => $entry->id,
        'batch_id' => $jobBatchId,
    ]);

    CreditUsage::factory()->create([
        'user_id' => $user->id,
        'entry_id' => $entry->id,
        'credits' => 1,
    ]);

    actingAs($user)
        ->getJson(route('credits'))
        ->assertStatus(200)
        ->assertJsonPath('usages.data.0.entry.latest_job_batch.job_batch.id', $jobBatchId)
        ->assertJsonPath('usages.data.0.entry.latest_job_batch.job_batch.failed_job_details.0.exception', 'Test Exception')
        ->assertJsonPath('current_credits', 50);
});
