<?php

declare(strict_types=1);

use FuteBus\Payment\Http\Controllers\ManualRefundController;
use FuteBus\StaffDashboard\Http\Middleware\EnsureStaff;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', EnsureStaff::class, 'permission:payment.view'])
    ->prefix('nhan-vien/hoan-tien')->name('staff.refunds.')->group(function (): void {
        Route::get('/', [ManualRefundController::class, 'index'])->name('index');
        Route::get('/{refund}', [ManualRefundController::class, 'show'])->whereNumber('refund')->name('show');
        Route::patch('/{refund}', [ManualRefundController::class, 'update'])->whereNumber('refund')
            ->middleware(['permission:payment.refund', 'throttle:15,1'])->name('update');
    });

Route::view('/nhan-vien', 'StaffDashboard::staff')
    ->middleware(['auth', EnsureStaff::class])->name('staff.dashboard');

Route::redirect('/nhan-vien/dashboard', '/nhan-vien')->middleware(['auth', EnsureStaff::class]);

Route::view('/nhan-vien/thong-tin-tai-khoan', 'StaffDashboard::profile')
    ->middleware(['auth', EnsureStaff::class])->name('staff.profile');

Route::view('/nhan-vien/doi-mat-khau', 'StaffDashboard::password')
    ->middleware(['auth', EnsureStaff::class])->name('staff.password');
