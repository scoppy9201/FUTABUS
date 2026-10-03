<?php

declare(strict_types=1);

use FuteBus\Profile\Http\Controllers\PasswordController;
use FuteBus\Profile\Http\Controllers\ProfileController;
use FuteBus\Profile\Http\Controllers\TicketHistoryController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/thong-tin-tai-khoan/thong-tin-chung', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/thong-tin-tai-khoan/thong-tin-chung', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/thong-tin-tai-khoan/anh-dai-dien', [ProfileController::class, 'avatar'])->name('profile.avatar');
    Route::get('/thong-tin-tai-khoan/lich-su-mua-ve', [TicketHistoryController::class, 'index'])->name('profile.tickets.index');
    Route::get('/thong-tin-tai-khoan/lich-su-mua-ve/{booking}', [TicketHistoryController::class, 'show'])
        ->whereNumber('booking')->name('profile.tickets.show');
    Route::get('/thong-tin-tai-khoan/dat-lai-mat-khau', [PasswordController::class, 'edit'])->name('profile.password.edit');
    Route::put('/thong-tin-tai-khoan/dat-lai-mat-khau', [PasswordController::class, 'update'])
        ->middleware('throttle:5,1')->name('profile.password.update');
});
