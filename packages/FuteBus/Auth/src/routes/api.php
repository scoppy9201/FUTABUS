<?php

declare(strict_types=1);

use FuteBus\Auth\Http\Controllers\Api\OtpController;
use FuteBus\Auth\Http\Controllers\Api\TokenController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::post('/registration-challenges', [OtpController::class, 'startRegistration'])->middleware('throttle:5,1')->name('registration-challenges.store');
    Route::post('/registration-challenges/resend', [OtpController::class, 'resendRegistration'])->middleware('throttle:5,1')->name('registration-challenges.resend');
    Route::post('/registration-challenges/verify', [OtpController::class, 'verifyRegistration'])->middleware('throttle:10,1')->name('registration-challenges.verify');
    Route::post('/registrations', [OtpController::class, 'register'])->middleware('throttle:5,1')->name('registrations.store');
    Route::post('/password-recovery-challenges', [OtpController::class, 'startRecovery'])->middleware('throttle:5,1')->name('password-recovery-challenges.store');
    Route::post('/password-recovery-challenges/resend', [OtpController::class, 'resendRecovery'])->middleware('throttle:5,1')->name('password-recovery-challenges.resend');
    Route::post('/password-recovery-challenges/verify', [OtpController::class, 'verifyRecovery'])->middleware('throttle:10,1')->name('password-recovery-challenges.verify');
    Route::post('/password-resets', [OtpController::class, 'resetPassword'])->middleware('throttle:5,1')->name('password-resets.store');
    Route::post('/tokens', [TokenController::class, 'store'])->middleware('throttle:10,1')->name('tokens.store');
    Route::delete('/tokens/current', [TokenController::class, 'destroy'])
        ->middleware('auth:sanctum')->name('tokens.destroy');
});
