<?php

declare(strict_types=1);

use FuteBus\StaffDashboard\Http\Middleware\EnsureStaff;
use FuteBus\StaffTicketProcessing\Http\Controllers\StaffExchangeController;
use FuteBus\StaffTicketProcessing\Http\Controllers\StaffPaymentController;
use FuteBus\StaffTicketProcessing\Http\Middleware\EnsureTicketAccess;
use FuteBus\StaffTicketProcessing\Http\Middleware\EnsureTicketOperator;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', EnsureTicketOperator::class.':booking.create'])->group(function (): void {
    Route::post('/nhan-vien/ban-ve', [StaffPaymentController::class, 'store'])
        ->middleware('throttle:staff-ticket-sales')->name('staff.ticket-sales.store');
    Route::delete('/nhan-vien/ban-ve/{sale}', [StaffPaymentController::class, 'destroy'])
        ->whereUuid('sale')->name('staff.ticket-sales.destroy');
});

Route::view('/nhan-vien/tra-cuu-ve', 'StaffTicketProcessing::lookup')
    ->middleware(['auth', EnsureStaff::class, 'permission:ticket.view'])->name('staff.ticket-lookup');

Route::view('/nhan-vien/xu-ly-ve', 'StaffTicketProcessing::tickets')
    ->middleware(['auth', EnsureStaff::class, EnsureTicketAccess::class])->name('staff.tickets');

Route::view('/nhan-vien/ban-ve', 'StaffTicketProcessing::sale')
    ->middleware(['auth', EnsureTicketOperator::class])->name('staff.tickets.sale');

Route::get('/nhan-vien/thanh-toan/{sale}', StaffPaymentController::class)->whereUuid('sale')
    ->middleware(['auth', EnsureTicketOperator::class.':booking.create'])->name('staff.tickets.payment');

Route::middleware(['auth', EnsureStaff::class, EnsureTicketOperator::class.':ticket.exchange', 'permission:ticket.view'])->group(function (): void {
    Route::view('/nhan-vien/doi-ve', 'StaffTicketProcessing::exchange')->name('staff.tickets.exchange');
    Route::get('/nhan-vien/doi-ve/ve/{ticket}', [StaffExchangeController::class, 'context'])
        ->whereNumber('ticket')->name('staff.tickets.exchange.context');
    Route::get('/nhan-vien/doi-ve/ve/{ticket}/chuyen', [StaffExchangeController::class, 'trips'])
        ->whereNumber('ticket')->middleware('throttle:60,1')->name('staff.tickets.exchange.trips');
});
