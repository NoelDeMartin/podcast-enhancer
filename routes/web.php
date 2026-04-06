<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EntryController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\FeedRssController;
use App\Http\Controllers\RssImportController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::get('feeds/{feed}.xml', FeedRssController::class)->name('feeds.rss');
Route::get('entries/{entry}/file', [EntryController::class, 'file'])->name('entries.file');
Route::get('entries/{entry}/chapters.json', [EntryController::class, 'chapters'])->name('entries.chapters');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('feeds', FeedController::class)->only(['store', 'show', 'update', 'destroy']);
    Route::resource('entries', EntryController::class)->only(['store', 'update', 'destroy']);
    Route::post('entries/{entry}/produce', [EntryController::class, 'produce'])->name('entries.produce');

    Route::post('feeds/{feed}/import-rss/fetch', [RssImportController::class, 'fetch'])->name('feeds.import-rss.fetch');
    Route::post('feeds/{feed}/import-rss/store', [RssImportController::class, 'store'])->name('feeds.import-rss.store');
});

require __DIR__.'/settings.php';
