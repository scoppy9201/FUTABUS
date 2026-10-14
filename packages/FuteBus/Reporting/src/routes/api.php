<?php

declare(strict_types=1);

use FuteBus\Auth\Http\Middleware\EnsureApiToken;
use FuteBus\Reporting\Http\Controllers\ReportingController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->middleware(['auth:sanctum', EnsureApiToken::class])->group(function (): void {
    Route::get('/reports', [ReportingController::class, 'data'])->name('reports.index');
});
