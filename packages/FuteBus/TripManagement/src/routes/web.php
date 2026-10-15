<?php

declare(strict_types=1);

use FuteBus\TripManagement\Http\Controllers\TripController;
use FuteBus\TripManagement\Http\Controllers\TripScheduleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('quan-tri/chuyen-xe')->group(function () {
    Route::get('/lich-trinh', [TripScheduleController::class, 'index'])->middleware('permission:trip_schedule.view')->name('trip-management.schedules.index');
    Route::post('/lich-trinh', [TripScheduleController::class, 'store'])->middleware('permission:trip_schedule.create')->name('trip-management.schedules.store');
    Route::put('/lich-trinh/{id}', [TripScheduleController::class, 'update'])->middleware('permission:trip_schedule.update')->name('trip-management.schedules.update');
    Route::delete('/lich-trinh/{id}', [TripScheduleController::class, 'destroy'])->middleware('permission:trip_schedule.delete')->name('trip-management.schedules.destroy');

    Route::get('/', [TripController::class, 'index'])->middleware('permission:trip.view')->name('trip-management.trips.index');
    Route::post('/', [TripController::class, 'store'])->middleware('permission:trip.create')->name('trip-management.trips.store');
    Route::put('/{id}', [TripController::class, 'update'])->whereNumber('id')->middleware('permission:trip.update')->name('trip-management.trips.update');
    Route::delete('/{id}', [TripController::class, 'destroy'])->whereNumber('id')->name('trip-management.trips.destroy');

});
