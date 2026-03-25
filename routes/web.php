<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EntryController;
use App\Http\Controllers\FeedController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('feeds', FeedController::class)->only(['store', 'show', 'update', 'destroy']);
    Route::resource('entries', EntryController::class)->only(['store', 'update', 'destroy']);
    Route::get('entries/{entry}/file', [EntryController::class, 'file'])->name('entries.file');
});

require __DIR__.'/settings.php';
