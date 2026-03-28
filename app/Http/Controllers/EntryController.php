<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEntryRequest;
use App\Http\Requests\UpdateEntryRequest;
use App\Jobs\TranscribeEntryJob;
use App\Models\Entry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

class EntryController extends Controller
{
    public function store(StoreEntryRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('file')) {
            $validated['file_path'] = $request->file('file')->store('entries');
        }

        $entry = Entry::create($validated);

        if ($entry->file_path) {
            $this->dispatchTranscriptionBatch($entry);
        }

        return redirect()->back()->with('success', 'Entry created successfully.');
    }

    public function update(UpdateEntryRequest $request, Entry $entry): RedirectResponse
    {
        $validated = $request->validated();

        $fileChanged = false;

        if ($request->hasFile('file')) {
            if ($entry->file_path) {
                Storage::delete($entry->file_path);
            }
            $validated['file_path'] = $request->file('file')->store('entries');
            $fileChanged = true;
        } elseif ($request->boolean('delete_file') && $entry->file_path) {
            Storage::delete($entry->file_path);
            $validated['file_path'] = null;
            $validated['transcription'] = null;
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
        if ($entry->file_path) {
            Storage::delete($entry->file_path);
        }

        $entry->delete();

        return redirect()->back()->with('success', 'Entry deleted successfully.');
    }

    public function transcribe(Entry $entry): RedirectResponse
    {
        if (! $entry->file_path) {
            abort(422, 'No audio file attached to this entry.');
        }

        $this->dispatchTranscriptionBatch($entry);

        return redirect()->back()->with('success', 'Transcription queued successfully.');
    }

    public function file(Entry $entry)
    {
        if (! $entry->file_path) {
            abort(404);
        }

        return Storage::response($entry->file_path);
    }

    private function dispatchTranscriptionBatch(Entry $entry): void
    {
        $batch = Bus::batch([new TranscribeEntryJob($entry)])
            ->name('Transcribe Entry: '.$entry->id)
            ->dispatch();

        $entry->jobBatches()->create(['batch_id' => $batch->id]);
    }
}
