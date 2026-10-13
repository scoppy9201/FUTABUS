<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Staff\Dashboard\Http\Middleware\EnsureStaff;

Route::view('/nhan-vien', 'StaffDashboard::staff')
    ->middleware(['auth', EnsureStaff::class])->name('staff.dashboard');

Route::redirect('/nhan-vien/dashboard', '/nhan-vien')->middleware(['auth', EnsureStaff::class]);

Route::view('/nhan-vien/thong-tin-tai-khoan', 'StaffDashboard::profile')
    ->middleware(['auth', EnsureStaff::class])->name('staff.profile');

Route::view('/nhan-vien/doi-mat-khau', 'StaffDashboard::password')
    ->middleware(['auth', EnsureStaff::class])->name('staff.password');
