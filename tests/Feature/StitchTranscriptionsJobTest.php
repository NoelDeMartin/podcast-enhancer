<?php

use App\Jobs\StitchTranscriptionsJob;
use App\Models\Entry;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

it('stitches transcription chunks and saves the result', function () {
    Storage::fake();

    $entry = Entry::factory()->create();

    // We need a real batch in the database for the job to find it
    $batch = Bus::batch([])->dispatch();

    // Create fake chunks
    $chunk0 = [
        ['text' => 'Chunk 0 segment', 'start_seconds' => 10, 'end_seconds' => 20],
        ['text' => 'Chunk 0 overlap', 'start_seconds' => 1810, 'end_seconds' => 1820], // Should be filtered out
    ];

    $chunk1 = [
        ['text' => 'Chunk 1 segment', 'start_seconds' => 1810, 'end_seconds' => 1820],
    ];

    $transcriptionsDir = "tmp/batch-{$batch->id}/transcriptions";
    Storage::put("{$transcriptionsDir}/chunk_0.json", json_encode($chunk0));
    Storage::put("{$transcriptionsDir}/chunk_1.json", json_encode($chunk1));

    $job = new StitchTranscriptionsJob($entry, $batch->id);
    $job->withBatchId($batch->id);

    $job->handle();

    $entry->refresh();
    expect($entry->transcription_path)->not->toBeNull();
    Storage::assertExists($entry->transcription_path);

    $stitched = json_decode(Storage::get($entry->transcription_path), true);
    expect($stitched)->toHaveCount(2);
    expect($stitched[0]['text'])->toBe('Chunk 0 segment');
    expect($stitched[1]['text'])->toBe('Chunk 1 segment');

    // Verify cleanup
    Storage::assertMissing("tmp/batch-{$batch->id}");
});
