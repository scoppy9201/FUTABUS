<?php

declare(strict_types=1);

use FuteBus\TripManagement\Http\Controllers\TripScheduleController;
use Illuminate\Support\Facades\Route;
use FuteBus\TripManagement\Http\Controllers\TripController;

Route::middleware(['web', 'auth'])->prefix('quan-tri/chuyen-xe')->group(function () {
    Route::get('/lich-trinh', [TripScheduleController::class, 'index'])->name('trip-management.schedules.index');
    Route::post('/lich-trinh', [TripScheduleController::class, 'store'])->name('trip-management.schedules.store');
    Route::put('/lich-trinh/{id}', [TripScheduleController::class, 'update'])->name('trip-management.schedules.update');
    Route::delete('/lich-trinh/{id}', [TripScheduleController::class, 'destroy'])->name('trip-management.schedules.destroy');

    Route::get('/', [TripController::class, 'index'])->name('trip-management.trips.index');
    Route::post('/', [TripController::class, 'store'])->name('trip-management.trips.store');
    Route::put('/{id}', [TripController::class, 'update'])->whereNumber('id')->name('trip-management.trips.update');
    Route::delete('/{id}', [TripController::class, 'destroy'])->whereNumber('id')->name('trip-management.trips.destroy');
});