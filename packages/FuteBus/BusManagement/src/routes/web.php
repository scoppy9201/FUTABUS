<?php

declare(strict_types=1);

use FuteBus\BusManagement\Http\Controllers\BusController;
use FuteBus\BusManagement\Http\Controllers\DocumentTypeController;
use FuteBus\BusManagement\Http\Controllers\VehicleDocumentController;
use FuteBus\BusManagement\Http\Controllers\VehicleTypeController;
use FuteBus\Notification\Http\Middleware\CaptureCompanyActivity;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', CaptureCompanyActivity::class])->prefix('quan-tri/phuong-tien')->group(function () {

    // Loại phương tiện
    Route::get('/loai-phuong-tien', [VehicleTypeController::class, 'index'])
        ->name('bus-management.vehicle-types.index');
    Route::post('/loai-phuong-tien', [VehicleTypeController::class, 'store'])
        ->name('bus-management.vehicle-types.store');
    Route::put('/loai-phuong-tien/{id}', [VehicleTypeController::class, 'update'])
        ->name('bus-management.vehicle-types.update');
    Route::delete('/loai-phuong-tien/{id}', [VehicleTypeController::class, 'destroy'])
        ->name('bus-management.vehicle-types.destroy');
    Route::patch('/loai-phuong-tien/{id}/toggle', [VehicleTypeController::class, 'toggle'])
        ->name('bus-management.vehicle-types.toggle');

    // Thông tin xe
    Route::get('/thong-tin-xe', [BusController::class, 'index'])
        ->name('bus-management.buses.index');
    Route::post('/thong-tin-xe', [BusController::class, 'store'])
        ->name('bus-management.buses.store');
    Route::put('/thong-tin-xe/{id}', [BusController::class, 'update'])
        ->name('bus-management.buses.update');
    Route::delete('/thong-tin-xe/{id}', [BusController::class, 'destroy'])
        ->name('bus-management.buses.destroy');
    Route::patch('/thong-tin-xe/{id}/toggle', [BusController::class, 'toggle'])
        ->name('bus-management.buses.toggle');

    // Loại giấy tờ
    Route::get('/loai-giay-to', [DocumentTypeController::class, 'index'])
        ->name('bus-management.document-types.index');
    Route::post('/loai-giay-to', [DocumentTypeController::class, 'store'])
        ->name('bus-management.document-types.store');
    Route::put('/loai-giay-to/{id}', [DocumentTypeController::class, 'update'])
        ->name('bus-management.document-types.update');
    Route::delete('/loai-giay-to/{id}', [DocumentTypeController::class, 'destroy'])
        ->name('bus-management.document-types.destroy');
    Route::patch('/loai-giay-to/{id}/toggle', [DocumentTypeController::class, 'toggle'])
        ->name('bus-management.document-types.toggle');

    // Hồ sơ giấy tờ xe
    Route::get('/ho-so-giay-to', [VehicleDocumentController::class, 'index'])
        ->name('bus-management.vehicle-documents.index');
    Route::post('/ho-so-giay-to', [VehicleDocumentController::class, 'store'])
        ->name('bus-management.vehicle-documents.store');
    Route::get('/ho-so-giay-to/{id}/tep', [VehicleDocumentController::class, 'file'])
        ->name('bus-management.vehicle-documents.file');
    Route::put('/ho-so-giay-to/{id}', [VehicleDocumentController::class, 'update'])
        ->name('bus-management.vehicle-documents.update');
    Route::post('/ho-so-giay-to/{id}/gia-han', [VehicleDocumentController::class, 'renew'])
        ->name('bus-management.vehicle-documents.renew');
    Route::delete('/ho-so-giay-to/{id}', [VehicleDocumentController::class, 'deactivate'])
        ->name('bus-management.vehicle-documents.deactivate');
    Route::delete('/ho-so-giay-to/{id}/xoa', [VehicleDocumentController::class, 'destroy'])
        ->name('bus-management.vehicle-documents.destroy');
});
