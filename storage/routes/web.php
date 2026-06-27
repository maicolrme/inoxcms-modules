<?php

use Illuminate\Support\Facades\Route;
use Inox\Storage\Livewire\MediaManager;
use Inox\Storage\Livewire\MediaSettings;

Route::middleware(['web', 'auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/storage', MediaManager::class)->name('storage.index');
    Route::get('/storage/settings', MediaSettings::class)->name('storage.settings');
});
