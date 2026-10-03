<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use FuteBus\Auth\Http\Controllers\LoginController;
use FuteBus\Auth\Http\Controllers\RegistrationController;
use FuteBus\Auth\Http\Controllers\PasswordRecoveryController;

Route::middleware('guest')->group(function (): void {
    Route::view('/dang-nhap', 'Auth::login')->name('login');
    Route::post('/dang-nhap', [LoginController::class, 'store'])->middleware('throttle:10,1')->name('login.store');
    Route::get('/dang-ky', [RegistrationController::class, 'show'])->name('register');
    Route::post('/dang-ky/email', [RegistrationController::class, 'sendEmail'])->middleware('throttle:5,1')->name('register.email');
    Route::post('/dang-ky/email/gui-lai', [RegistrationController::class, 'resendEmail'])->middleware('throttle:5,1')->name('register.email.resend');
    Route::post('/dang-ky/email/xac-thuc', [RegistrationController::class, 'verifyEmail'])->middleware('throttle:10,1')->name('register.email.verify');
    Route::post('/dang-ky/mat-khau', [RegistrationController::class, 'submitPassword'])->middleware('throttle:10,1')->name('register.password');
    Route::post('/dang-ky/hoan-tat', [RegistrationController::class, 'submitProfile'])->middleware('throttle:10,1')->name('register.profile');
    Route::get('/quen-mat-khau', [PasswordRecoveryController::class, 'show'])->name('password.request');
    Route::post('/quen-mat-khau/email', [PasswordRecoveryController::class, 'requestCode'])->middleware('throttle:5,1')->name('password.email');
    Route::post('/quen-mat-khau/email/gui-lai', [PasswordRecoveryController::class, 'resendCode'])->middleware('throttle:5,1')->name('password.email.resend');
    Route::post('/quen-mat-khau/email/xac-thuc', [PasswordRecoveryController::class, 'verifyCode'])->middleware('throttle:10,1')->name('password.email.verify');
    Route::post('/quen-mat-khau/mat-khau', [PasswordRecoveryController::class, 'resetPassword'])->middleware('throttle:10,1')->name('password.update');
});
