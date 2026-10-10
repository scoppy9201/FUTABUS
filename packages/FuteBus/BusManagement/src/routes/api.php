<?php

declare(strict_types=1);

use FuteBus\Auth\Http\Middleware\EnsureApiAdmin;
use FuteBus\Auth\Http\Middleware\EnsureApiToken;
use FuteBus\BusManagement\Http\Controllers\Api\VehicleTypeController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/admin')->name('api.v1.admin.')
    ->middleware(['auth:sanctum', EnsureApiToken::class, EnsureApiAdmin::class])
    ->group(function (): void {
        Route::get('/vehicle-types', [VehicleTypeController::class, 'index'])->name('vehicle-types.index');
        Route::post('/vehicle-types', [VehicleTypeController::class, 'store'])->name('vehicle-types.store');
        Route::get('/vehicle-types/{vehicleType}', [VehicleTypeController::class, 'show'])
            ->whereNumber('vehicleType')->name('vehicle-types.show');
        Route::put('/vehicle-types/{vehicleType}', [VehicleTypeController::class, 'update'])
            ->whereNumber('vehicleType')->name('vehicle-types.update');
        Route::delete('/vehicle-types/{vehicleType}', [VehicleTypeController::class, 'destroy'])
            ->whereNumber('vehicleType')->name('vehicle-types.destroy');
    });
