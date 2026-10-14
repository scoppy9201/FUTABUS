<?php

declare(strict_types=1);

use FuteBus\Notification\Http\Middleware\CaptureCompanyActivity;
use FuteBus\StaffDashboard\Http\Middleware\EnsureStaff;
use FuteBus\StaffTicketProcessing\Http\Controllers\StaffPaymentController;
use FuteBus\StaffTicketProcessing\Http\Middleware\EnsureTicketOperator;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', EnsureTicketOperator::class.':booking.create', CaptureCompanyActivity::class])->group(function (): void {
    Route::post('/nhan-vien/ban-ve', [StaffPaymentController::class, 'store'])
        ->middleware('throttle:staff-ticket-sales')->name('staff.ticket-sales.store');
    Route::delete('/nhan-vien/ban-ve/{sale}', [StaffPaymentController::class, 'destroy'])
        ->whereUuid('sale')->name('staff.ticket-sales.destroy');
});

Route::view('/nhan-vien/tra-cuu-ve', 'StaffTicketProcessing::lookup')
    ->middleware(['auth', EnsureStaff::class, 'permission:ticket.view'])->name('staff.ticket-lookup');

Route::view('/nhan-vien/xu-ly-ve', 'StaffTicketProcessing::tickets')
    ->middleware(['auth', EnsureStaff::class])->name('staff.tickets');

Route::view('/nhan-vien/ban-ve', 'StaffTicketProcessing::sale')
    ->middleware(['auth', EnsureTicketOperator::class])->name('staff.tickets.sale');

Route::get('/nhan-vien/thanh-toan/{sale}', StaffPaymentController::class)->whereUuid('sale')
    ->middleware(['auth', EnsureTicketOperator::class.':booking.create'])->name('staff.tickets.payment');
