<?php

declare(strict_types=1);

use FuteBus\Auth\Http\Controllers\Api\TokenController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::post('/tokens', [TokenController::class, 'store'])->middleware('throttle:10,1')->name('tokens.store');
    Route::delete('/tokens/current', [TokenController::class, 'destroy'])
        ->middleware('auth:sanctum')->name('tokens.destroy');
});
