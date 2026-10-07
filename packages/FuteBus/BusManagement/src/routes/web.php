<?php

declare(strict_types=1);

use FuteBus\BusManagement\Http\Controllers\BusController;
use FuteBus\BusManagement\Http\Controllers\VehicleTypeController;
use Illuminate\Support\Facades\Route;
use FuteBus\BusManagement\Http\Controllers\DocumentTypeController;
use FuteBus\BusManagement\Http\Controllers\VehicleDocumentController;

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

        // Loại giấy tờ
    Route::get('/loai-giay-to', [DocumentTypeController::class, 'index'])
        ->name('bus-management.document-types.index');
    Route::post('/loai-giay-to', [DocumentTypeController::class, 'store'])
        ->name('bus-management.document-types.store');
    Route::put('/loai-giay-to/{id}', [DocumentTypeController::class, 'update'])
        ->name('bus-management.document-types.update');
    Route::delete('/loai-giay-to/{id}', [DocumentTypeController::class, 'destroy'])
        ->name('bus-management.document-types.destroy');

    // Hồ sơ giấy tờ xe
    Route::get('/ho-so-giay-to', [VehicleDocumentController::class, 'index'])
        ->name('bus-management.vehicle-documents.index');
    Route::post('/ho-so-giay-to', [VehicleDocumentController::class, 'store'])
        ->name('bus-management.vehicle-documents.store');
    Route::post('/ho-so-giay-to/{id}/gia-han', [VehicleDocumentController::class, 'renew'])
        ->name('bus-management.vehicle-documents.renew');
    Route::delete('/ho-so-giay-to/{id}', [VehicleDocumentController::class, 'deactivate'])
        ->name('bus-management.vehicle-documents.deactivate');
});