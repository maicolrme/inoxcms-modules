<?php

use Illuminate\Support\Facades\Route;
use Inox\Api\Livewire\ApiTokens;
use Inox\Api\Livewire\ApiSettings;
use Inox\Api\Livewire\ApiLog;
use Inox\Api\Livewire\ApiDynamicModels;
use Inox\Api\Livewire\RouteManager;

Route::middleware(['web', 'auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/settings',       ApiSettings::class)->name('settings');
        Route::get('/tokens',         ApiTokens::class)->name('tokens');
        Route::get('/dynamic-models', ApiDynamicModels::class)->name('dynamic-models');
        Route::get('/routes',         RouteManager::class)->name('routes');
        Route::get('/log',            ApiLog::class)->name('log');
    });
});
