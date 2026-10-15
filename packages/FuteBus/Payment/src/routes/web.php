<?php

declare(strict_types=1);

use FuteBus\Payment\Http\Controllers\ManualRefundController;
use FuteBus\Payment\Http\Controllers\TicketExchangeController;
use FuteBus\Payment\Http\Controllers\TripPaymentPreviewController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('doi-ve/{booking}')->whereNumber('booking')->name('exchange.')->group(function (): void {
    Route::get('/', [TicketExchangeController::class, 'show'])->name('show');
    Route::get('/chuyen', [TicketExchangeController::class, 'trips'])->middleware('throttle:60,1')->name('trips');
    Route::post('/giu-ghe', [TicketExchangeController::class, 'hold'])->middleware('throttle:20,1')->name('hold');
    Route::post('/{token}/xac-nhan', [TicketExchangeController::class, 'complete'])->whereUuid('token')->middleware('throttle:10,1')->name('complete');
    Route::post('/{token}/bo-giu', [TicketExchangeController::class, 'release'])->whereUuid('token')->name('release');
});
Route::get('/quan-tri/doi-ve', [TicketExchangeController::class, 'manage'])->middleware('auth')->name('exchange.manage');
Route::get('/nhan-vien/doi-ve', [TicketExchangeController::class, 'manage'])->middleware('auth')->name('staff.exchange.manage');

Route::middleware(['auth', 'permission:payment.view'])->prefix('quan-tri/hoan-tien')->name('refunds.')->group(function (): void {
    Route::get('/', [ManualRefundController::class, 'index'])->name('index');
    Route::get('/{refund}', [ManualRefundController::class, 'show'])->whereNumber('refund')->name('show');
    Route::patch('/{refund}', [ManualRefundController::class, 'update'])->whereNumber('refund')
        ->middleware(['permission:payment.refund', 'throttle:15,1'])->name('update');
});

Route::view('/huong-dan-nap-tien-tren-app', 'Payment::topup-guide')->name('topup-guide');

Route::post('/dat-ve/chon-chuyen/{trip}/thanh-toan', [TripPaymentPreviewController::class, 'store'])
    ->whereNumber('trip')->name('trip-booking.payment.store');
Route::get('/thanh-toan/{draft}', [TripPaymentPreviewController::class, 'show'])
    ->name('trip-payment-preview.show');
Route::post('/thanh-toan/{draft}/huy', [TripPaymentPreviewController::class, 'cancel'])
    ->name('trip-payment-preview.cancel');
Route::post('/thanh-toan/{draft}/tao-ma', [TripPaymentPreviewController::class, 'activate'])
    ->name('trip-payment-preview.activate');
Route::get('/thanh-toan/{draft}/trang-thai', [TripPaymentPreviewController::class, 'status'])
    ->middleware('throttle:60,1')->name('trip-payment-preview.status');
