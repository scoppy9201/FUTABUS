<?php

declare(strict_types=1);

use FuteBus\Reporting\Http\Controllers\ReportingController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('quan-tri/bao-cao')->name('reporting.')->group(function (): void {
    Route::get('/', [ReportingController::class, 'index'])->name('index');
    Route::get('/xuat', [ReportingController::class, 'export'])->name('export');
});
