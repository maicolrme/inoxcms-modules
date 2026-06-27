<?php

use Illuminate\Support\Facades\Route;
use Inox\SchemaStudio\Http\Controllers\Api\DynamicModelController;

$authType = config('inox.api.auth_type', 'sanctum');
$middleware = $authType === 'none' ? ['api'] : ['api', 'auth:sanctum'];

Route::middleware(array_merge($middleware, ['api.logger']))->prefix('api/dynamic')->name('api.dynamic.')->group(function () {
    Route::get('/{model}', [DynamicModelController::class, 'index'])->name('index');
    Route::post('/{model}', [DynamicModelController::class, 'store'])->name('store');
    Route::get('/{model}/{id}', [DynamicModelController::class, 'show'])->name('show');
    Route::put('/{model}/{id}', [DynamicModelController::class, 'update'])->name('update');
    Route::delete('/{model}/{id}', [DynamicModelController::class, 'destroy'])->name('destroy');
});
