<?php

declare(strict_types=1);

use FuteBus\Cancellation\Http\Controllers\TicketCancellationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('thong-tin-tai-khoan/lich-su-mua-ve/{booking}/huy-ve')->whereNumber('booking')->group(function (): void {
    Route::get('/', [TicketCancellationController::class, 'show'])->name('cancellation.show');
    Route::post('/bao-gia', [TicketCancellationController::class, 'quote'])->middleware('throttle:30,1')->name('cancellation.quote');
    Route::post('/', [TicketCancellationController::class, 'store'])->middleware('throttle:5,1')->name('cancellation.store');
    Route::get('/ket-qua', [TicketCancellationController::class, 'status'])->middleware('throttle:30,1')->name('cancellation.status');
});

Route::middleware('web')->group(function () {
    Route::get('/Cancellation', function () {
        return view('Cancellation::index');
    })->name('Cancellation.index');
});
