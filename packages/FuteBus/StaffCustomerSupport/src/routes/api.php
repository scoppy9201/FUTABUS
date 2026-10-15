<?php

declare(strict_types=1);

use App\Http\Middleware\SetLocale;
use FuteBus\StaffCustomerSupport\Http\Controllers\Api\SupportRequestController;
use FuteBus\StaffDashboard\Http\Middleware\EnsureStaff;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware([
    'auth:sanctum', EnsureStaff::class, SetLocale::class,
])->group(function (): void {
    Route::prefix('staff/support-requests')->name('api.v1.staff.support.')->middleware('permission:support.view')->group(function (): void {
        $controller = SupportRequestController::class;
        Route::get('/', [$controller, 'index'])->name('index');
        Route::get('/{report}', [$controller, 'show'])->whereNumber('report')->name('show');
        Route::get('/{report}/attachments/{attachment}', [$controller, 'attachment'])
            ->whereNumber(['report', 'attachment'])->name('attachments');
        Route::get('/{report}/responses/{response}', [$controller, 'response'])
            ->whereNumber(['report', 'response'])->name('responses.show');
        Route::get('/{report}/events/{event}', [$controller, 'event'])->whereNumber(['report', 'event'])->name('events.show');
        Route::post('/{report}/transfers', [$controller, 'transfer'])->whereNumber('report')
            ->middleware(['permission:support.respond', 'throttle:15,1'])->name('transfers.store');
        Route::post('/{report}/notes', [$controller, 'note'])->whereNumber('report')
            ->middleware(['permission:support.respond', 'throttle:15,1'])->name('notes.store');
        Route::put('/{report}/assignee', [$controller, 'claim'])->whereNumber('report')
            ->middleware(['permission:support.respond', 'throttle:15,1'])->name('claim');
        Route::post('/{report}/responses', [$controller, 'respond'])->whereNumber('report')
            ->middleware(['permission:support.respond', 'throttle:15,1'])->name('responses.store');
    });
});
