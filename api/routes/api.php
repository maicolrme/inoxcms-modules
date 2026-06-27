<?php

use App\Http\Controllers\Api\TokenController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api'])->prefix('api')->group(function () {
    $authType = config('inox.api.auth_type', 'sanctum');
    $middleware = $authType === 'none' ? [] : ['auth:sanctum'];

    Route::middleware(array_merge($middleware, ['api.logger']))->group(function () {
        Route::get('tokens', [TokenController::class, 'index']);
        Route::post('tokens', [TokenController::class, 'store']);
        Route::delete('tokens/{id}', [TokenController::class, 'destroy']);
    });
});
