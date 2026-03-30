<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEntryRequest;
use App\Http\Requests\UpdateEntryRequest;
use App\Jobs\PrepareTranscriptionJob;
use App\Jobs\ProduceEntryJob;
use App\Jobs\StitchTranscriptionsJob;
use App\Models\Entry;
use App\Models\EntryJobBatch;
use Illuminate\Bus\Batch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

class EntryController extends Controller
{
    public function store(StoreEntryRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('file')) {
            $validated['audio_url'] = $request->file('file')->store('entries');
        }

        $entry = Entry::create($validated);

        if ($entry->audio_url) {
            $this->dispatchTranscriptionBatch($entry);
        }

        return redirect()->back()->with('success', 'Entry created successfully.');
    }

    public function update(UpdateEntryRequest $request, Entry $entry): RedirectResponse
    {
        $validated = $request->validated();

        $fileChanged = false;

        if ($request->hasFile('file')) {
            if ($entry->audio_url && ! filter_var($entry->audio_url, FILTER_VALIDATE_URL)) {
                Storage::delete($entry->audio_url);
            }
            if ($entry->transcription_path) {
                Storage::delete($entry->transcription_path);
                $validated['transcription_path'] = null;
            }
            $validated['audio_url'] = $request->file('file')->store('entries');
            $validated['summary'] = null;
            $validated['chapters'] = null;
            $fileChanged = true;
        } elseif ($request->boolean('delete_file') && $entry->audio_url) {
            if (! filter_var($entry->audio_url, FILTER_VALIDATE_URL)) {
                Storage::delete($entry->audio_url);
            }
            if ($entry->transcription_path) {
                Storage::delete($entry->transcription_path);
            }
            $validated['audio_url'] = null;
            $validated['transcription_path'] = null;
            $validated['summary'] = null;
            $validated['chapters'] = null;
        } elseif ($request->has('audio_url') && $request->audio_url !== $entry->audio_url) {
            if ($entry->audio_url && ! filter_var($entry->audio_url, FILTER_VALIDATE_URL)) {
                Storage::delete($entry->audio_url);
            }
            if ($entry->transcription_path) {
                Storage::delete($entry->transcription_path);
                $validated['transcription_path'] = null;
            }
            $validated['summary'] = null;
            $validated['chapters'] = null;
            $fileChanged = true;
        }

        unset($validated['delete_file']);

        $entry->update($validated);

        if ($fileChanged) {
            $this->dispatchTranscriptionBatch($entry);
        }

        return redirect()->back()->with('success', 'Entry updated successfully.');
    }

    public function destroy(Entry $entry): RedirectResponse
    {
        if ($entry->audio_url && ! filter_var($entry->audio_url, FILTER_VALIDATE_URL)) {
            Storage::delete($entry->audio_url);
        }

        if ($entry->transcription_path) {
            Storage::delete($entry->transcription_path);
        }

        $entry->delete();

        return redirect()->back()->with('success', 'Entry deleted successfully.');
    }

    public function produce(Entry $entry): RedirectResponse
    {
        if (! $entry->audio_url) {
            abort(422, 'No audio file attached to this entry.');
        }

        $this->dispatchTranscriptionBatch($entry);

        return redirect()->back()->with('success', 'Transcription queued successfully.');
    }

    public function file(Entry $entry)
    {
        if (! $entry->audio_url) {
            abort(404);
        }

        if (filter_var($entry->audio_url, FILTER_VALIDATE_URL)) {
            return redirect()->away($entry->audio_url);
        }

        return Storage::response($entry->audio_url);
    }

    public function chapters(Entry $entry): JsonResponse
    {
        if (! $entry->chapters) {
            abort(404);
        }

        return response()->json([
            'version' => '1.2.0',
            'chapters' => $entry->chapters,
        ]);
    }

    private function dispatchTranscriptionBatch(Entry $entry): void
    {
        $entryId = $entry->id;

        if (! $entryId) {
            throw new \Exception('Entry ID is missing before dispatching batch');
        }

        $batch = Bus::batch([
            new PrepareTranscriptionJob($entry),
        ])
            ->then(function (Batch $batch) use ($entryId) {
                $entry = Entry::find($entryId);

                if ($entry) {
                    $this->dispatchProductionBatch($entry, $batch->id);
                }
            })
            ->name('Process entry '.$entryId)
            ->dispatch();

        EntryJobBatch::forceCreate([
            'entry_id' => $entryId,
            'batch_id' => $batch->id,
        ]);
    }

    private function dispatchProductionBatch(Entry $entry, string $transcriptionBatchId): void
    {
        $batch = Bus::batch([
            [
                new StitchTranscriptionsJob($entry, $transcriptionBatchId),
                new ProduceEntryJob($entry),
            ],
        ])->dispatch();

        EntryJobBatch::forceCreate([
            'entry_id' => $entry->id,
            'batch_id' => $batch->id,
        ]);
    }
}
