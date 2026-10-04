<?php

declare(strict_types=1);

use FuteBus\Dashboard\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/quan-tri/{section}', [DashboardController::class, 'section'])
        ->whereIn('section', ['trips', 'routes', 'buses', 'bookings', 'customers', 'reports'])
        ->name('dashboard.section');
});
