<?php

declare(strict_types=1);

use App\Http\Middleware\SetLocale;
use FuteBus\Profile\Http\Controllers\Api\AccountController;
use Illuminate\Support\Facades\Route;
use Staff\Dashboard\Http\Controllers\Api\StaffDashboardController;
use Staff\Dashboard\Http\Controllers\Api\StaffPasswordController;
use Staff\Dashboard\Http\Middleware\EnsureStaff;

Route::prefix('v1')->middleware(['auth:sanctum', EnsureStaff::class, SetLocale::class])->group(function (): void {
    Route::get('/staff/dashboard', [StaffDashboardController::class, 'show'])->name('api.v1.staff.dashboard.show');
    Route::get('/staff/profile', [AccountController::class, 'show'])->name('api.v1.staff.profile.show');
    Route::put('/staff/profile', [AccountController::class, 'update'])->name('api.v1.staff.profile.update');
    Route::get('/staff/profile/avatar', [AccountController::class, 'avatar'])->name('api.v1.staff.profile.avatar');
    Route::put('/staff/profile/password', [StaffPasswordController::class, 'update'])->name('api.v1.staff.profile.password.update');
});
