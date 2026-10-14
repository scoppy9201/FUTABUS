<?php

declare(strict_types=1);

use FuteBus\Reporting\Http\Controllers\ReportingController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/quan-tri/bao-cao', [ReportingController::class, 'index'])->name('reporting.index');
    Route::get('/quan-tri/bao-cao/xuat', [ReportingController::class, 'export'])->name('reporting.export');
    Route::get('/Reporting', fn () => redirect()->route('reporting.index'))->name('Reporting.index');
});
