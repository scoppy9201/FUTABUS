<?php

declare(strict_types=1);

use FuteBus\Payment\Http\Controllers\TripPaymentPreviewController;
use Illuminate\Support\Facades\Route;

Route::post('/dat-ve/chon-chuyen/{trip}/thanh-toan', [TripPaymentPreviewController::class, 'store'])
    ->whereNumber('trip')->name('trip-booking.payment.store');
Route::get('/thanh-toan/{draft}', [TripPaymentPreviewController::class, 'show'])
    ->name('trip-payment-preview.show');
Route::get('/thanh-toan/{draft}/trang-thai', [TripPaymentPreviewController::class, 'status'])
    ->middleware('throttle:60,1')->name('trip-payment-preview.status');
