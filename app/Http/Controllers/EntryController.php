<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEntryRequest;
use App\Http\Requests\UpdateEntryRequest;
use App\Models\Entry;
use Illuminate\Http\RedirectResponse;

class EntryController extends Controller
{
    public function store(StoreEntryRequest $request): RedirectResponse
    {
        Entry::create($request->validated());

        return redirect()->back()->with('success', 'Entry created successfully.');
    }

    public function update(UpdateEntryRequest $request, Entry $entry): RedirectResponse
    {
        $entry->update($request->validated());

        return redirect()->back()->with('success', 'Entry updated successfully.');
    }

    public function destroy(Entry $entry): RedirectResponse
    {
        $entry->delete();

        return redirect()->back()->with('success', 'Entry deleted successfully.');
    }
}
