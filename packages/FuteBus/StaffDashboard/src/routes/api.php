<?php

declare(strict_types=1);

use App\Http\Middleware\SetLocale;
use FuteBus\Profile\Http\Controllers\Api\AccountController;
use FuteBus\StaffDashboard\Http\Controllers\Api\StaffActivityController;
use FuteBus\StaffDashboard\Http\Controllers\Api\StaffDashboardController;
use FuteBus\StaffDashboard\Http\Controllers\Api\StaffPasswordController;
use FuteBus\StaffDashboard\Http\Middleware\EnsureStaff;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['auth:sanctum', EnsureStaff::class, SetLocale::class])->group(function (): void {
    Route::get('/staff/dashboard', [StaffDashboardController::class, 'show'])->name('api.v1.staff.dashboard.show');
    Route::get('/staff/activity', StaffActivityController::class)->middleware('throttle:60,1')->name('api.v1.staff.activity');
    Route::get('/staff/me', [AccountController::class, 'show'])->name('api.v1.staff.me.show');
    Route::put('/staff/me', [AccountController::class, 'update'])->name('api.v1.staff.me.update');
    Route::get('/staff/me/avatar', [AccountController::class, 'avatar'])->name('api.v1.staff.me.avatar');
    Route::put('/staff/me/password', [StaffPasswordController::class, 'update'])->name('api.v1.staff.me.password.update');
});
