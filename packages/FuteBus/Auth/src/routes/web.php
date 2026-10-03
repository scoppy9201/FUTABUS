<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use FuteBus\Auth\Http\Controllers\LoginController;
use FuteBus\Auth\Http\Controllers\RegistrationController;

Route::middleware('guest')->group(function (): void {
    Route::view('/dang-nhap', 'Auth::login')->name('login');
    Route::post('/dang-nhap', [LoginController::class, 'store'])->middleware('throttle:10,1')->name('login.store');
    Route::get('/dang-ky', [RegistrationController::class, 'show'])->name('register');
    Route::post('/dang-ky/email', [RegistrationController::class, 'sendEmail'])->middleware('throttle:5,1')->name('register.email');
    Route::post('/dang-ky/email/gui-lai', [RegistrationController::class, 'resendEmail'])->middleware('throttle:5,1')->name('register.email.resend');
    Route::post('/dang-ky/email/xac-thuc', [RegistrationController::class, 'verifyEmail'])->middleware('throttle:10,1')->name('register.email.verify');
    Route::post('/dang-ky/mat-khau', [RegistrationController::class, 'submitPassword'])->middleware('throttle:10,1')->name('register.password');
    Route::post('/dang-ky/hoan-tat', [RegistrationController::class, 'submitProfile'])->middleware('throttle:10,1')->name('register.profile');
    Route::view('/quen-mat-khau', 'Auth::forgot-password')->name('password.request');
});
