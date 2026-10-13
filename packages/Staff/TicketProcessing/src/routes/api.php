<?php

declare(strict_types=1);

use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;
use Staff\TicketProcessing\Http\Controllers\Api\CounterSaleController;
use Staff\TicketProcessing\Http\Controllers\Api\TicketListController;
use Staff\TicketProcessing\Http\Middleware\EnsureTicketOperator;

Route::prefix('v1/staff')->name('api.v1.staff.')->middleware(['auth:sanctum', SetLocale::class])->group(function (): void {
    Route::get('/ticket-context', [CounterSaleController::class, 'context'])->middleware(EnsureTicketOperator::class)->name('tickets.context');
    Route::get('/tickets', TicketListController::class)->middleware(EnsureTicketOperator::class.':ticket.view')->name('tickets.index');
    Route::middleware(EnsureTicketOperator::class.':booking.create')->group(function (): void {
        Route::get('/ticket-trips', [CounterSaleController::class, 'trips'])->middleware('throttle:staff-ticket-trips')->name('tickets.trips');
        Route::get('/ticket-trips/{trip}/booking', [CounterSaleController::class, 'booking'])->whereNumber('trip')->middleware('throttle:staff-ticket-booking')->name('tickets.booking');
        Route::post('/ticket-sales', [CounterSaleController::class, 'store'])->middleware('throttle:staff-ticket-sales')->name('ticket-sales.store');
        Route::post('/ticket-previews', [CounterSaleController::class, 'preview'])->middleware('throttle:staff-ticket-previews')->name('ticket-previews.store');
        Route::post('/ticket-quotes', [CounterSaleController::class, 'quote'])->middleware('throttle:staff-ticket-quotes')->name('ticket-quotes.store');
        Route::get('/ticket-sales/{sale}', [CounterSaleController::class, 'show'])->whereUuid('sale')->name('ticket-sales.show');
        Route::delete('/ticket-sales/{sale}', [CounterSaleController::class, 'destroy'])->whereUuid('sale')->name('ticket-sales.destroy');
        Route::post('/ticket-sales/{sale}/cash', [CounterSaleController::class, 'cash'])->whereUuid('sale')->middleware('throttle:staff-ticket-sales')->name('ticket-sales.cash');
    });
});
