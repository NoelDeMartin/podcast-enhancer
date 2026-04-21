<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EntryController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\FeedRssController;
use App\Http\Controllers\FeedSyncController;
use App\Http\Controllers\RssImportController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::get('feeds/{feed}.xml', FeedRssController::class)->name('feeds.rss');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('feeds/sync', [FeedSyncController::class, 'store'])->name('feeds.sync.store');
    Route::post('feeds/{feed}/sync', [FeedSyncController::class, 'sync'])->name('feeds.sync');

    Route::resource('feeds', FeedController::class)->only(['store', 'show', 'update', 'destroy']);

    Route::resource('feeds.entries', EntryController::class)->only(['store', 'show', 'update', 'destroy'])->scoped()->names([
        'store' => 'entries.store',
        'show' => 'entries.show',
        'update' => 'entries.update',
        'destroy' => 'entries.destroy',
    ]);
    Route::post('feeds/{feed}/entries/{entry}/produce', [EntryController::class, 'produce'])->name('entries.produce')->scopeBindings();

    Route::prefix('feeds/{feed}')->group(function () {
        Route::post('import-rss/fetch', [RssImportController::class, 'fetch'])->name('feeds.import-rss.fetch');
        Route::post('import-rss/store', [RssImportController::class, 'store'])->name('feeds.import-rss.store');
    });
});

require __DIR__.'/settings.php';
