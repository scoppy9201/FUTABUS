<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Staff\Dashboard\Http\Middleware\EnsureStaff;
use Staff\TicketProcessing\Http\Controllers\StaffPaymentController;
use Staff\TicketProcessing\Http\Middleware\EnsureTicketOperator;

Route::view('/nhan-vien/xu-ly-ve', 'StaffTickets::tickets')
    ->middleware(['auth', EnsureStaff::class])->name('staff.tickets');

Route::view('/nhan-vien/ban-ve', 'StaffTickets::sale')
    ->middleware(['auth', EnsureTicketOperator::class])->name('staff.tickets.sale');

Route::get('/nhan-vien/thanh-toan/{sale}', StaffPaymentController::class)->whereUuid('sale')
    ->middleware(['auth', EnsureTicketOperator::class.':booking.create'])->name('staff.tickets.payment');
