<?php

declare(strict_types=1);

use FuteBus\TripSearch\Http\Controllers\Api\TripController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::get('/trips', TripController::class)->name('trips.index');
});
