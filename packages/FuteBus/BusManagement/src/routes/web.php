<?php

declare(strict_types=1);

use FuteBus\BusManagement\Http\Controllers\BusController;
use FuteBus\BusManagement\Http\Controllers\VehicleTypeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('quan-tri/phuong-tien')->group(function () {

    // Loại phương tiện
    Route::get('/loai-phuong-tien', [VehicleTypeController::class, 'index'])
        ->name('bus-management.vehicle-types.index');
    Route::post('/loai-phuong-tien', [VehicleTypeController::class, 'store'])
        ->name('bus-management.vehicle-types.store');
    Route::put('/loai-phuong-tien/{id}', [VehicleTypeController::class, 'update'])
        ->name('bus-management.vehicle-types.update');
    Route::delete('/loai-phuong-tien/{id}', [VehicleTypeController::class, 'destroy'])
        ->name('bus-management.vehicle-types.destroy');

    // Thông tin xe
    Route::get('/thong-tin-xe', [BusController::class, 'index'])
        ->name('bus-management.buses.index');
    Route::post('/thong-tin-xe', [BusController::class, 'store'])
        ->name('bus-management.buses.store');
    Route::put('/thong-tin-xe/{id}', [BusController::class, 'update'])
        ->name('bus-management.buses.update');
    Route::delete('/thong-tin-xe/{id}', [BusController::class, 'destroy'])
        ->name('bus-management.buses.destroy');
});