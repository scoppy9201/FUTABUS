<?php

declare(strict_types=1);

use FuteBus\Auth\Http\Middleware\EnsureApiToken;
use FuteBus\Profile\Http\Controllers\Api\AccountController;
use FuteBus\Profile\Http\Controllers\Api\BookingController;
use FuteBus\Profile\Http\Controllers\Api\PasswordController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->middleware(['auth:sanctum', EnsureApiToken::class])->group(function (): void {
    Route::get('/me', [AccountController::class, 'show'])->name('me.show');
    Route::put('/me', [AccountController::class, 'update'])->name('me.update');
    Route::get('/me/avatar', [AccountController::class, 'avatar'])->name('me.avatar');
    Route::put('/me/password', [PasswordController::class, 'update'])->name('me.password.update');
    Route::get('/me/bookings', [BookingController::class, 'index'])->name('me.bookings.index');
    Route::get('/me/bookings/{booking}', [BookingController::class, 'show'])
        ->whereNumber('booking')->name('me.bookings.show');
});
