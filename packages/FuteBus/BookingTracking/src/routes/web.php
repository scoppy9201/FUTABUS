<?php

declare(strict_types=1);

use FuteBus\BookingTracking\Http\Controllers\BookingMonitorController;
use FuteBus\StaffDashboard\Http\Middleware\EnsureStaff;
use Illuminate\Support\Facades\Route;

foreach (['bookings' => 'quan-tri/dat-ve', 'staff.bookings' => 'nhan-vien/dat-ve'] as $name => $prefix) {
    $middleware = ['auth', 'permission:booking.view'];
    if ($name === 'staff.bookings') {
        $middleware[] = EnsureStaff::class;
    }
    Route::middleware($middleware)->prefix($prefix)->name($name.'.')->group(function (): void {
        Route::get('/', [BookingMonitorController::class, 'index'])->name('index');
        Route::get('/{kind}/{record}', [BookingMonitorController::class, 'show'])
            ->whereIn('kind', ['booking', 'intent', 'outbound'])->whereNumber('record')->name('show');
    });
}
