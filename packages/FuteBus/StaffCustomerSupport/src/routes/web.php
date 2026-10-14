<?php

declare(strict_types=1);

use FuteBus\StaffCustomerSupport\Http\Controllers\SupportTriageController;
use FuteBus\StaffDashboard\Http\Middleware\EnsureStaff;
use Illuminate\Support\Facades\Route;

Route::view('/nhan-vien/ho-tro/{report?}', 'StaffCustomerSupport::support')->whereNumber('report')
    ->middleware(['auth', EnsureStaff::class, 'permission:support.view'])->name('staff.support');

Route::middleware('auth')->group(function (): void {
    Route::get('/quan-tri/ho-tro-khach-hang', [SupportTriageController::class, 'index'])
        ->name('staff.support-triage');
    Route::put('/quan-tri/ho-tro-khach-hang/{report}/nha-xe', [SupportTriageController::class, 'assign'])
        ->whereNumber('report')->middleware('throttle:15,1')->name('staff.support-triage.assign');
});
