<?php

declare(strict_types=1);

use App\Http\Middleware\SetLocale;
use FuteBus\StaffTicketProcessing\Http\Controllers\Api\CounterSaleController;
use FuteBus\StaffTicketProcessing\Http\Controllers\Api\TicketListController;
use FuteBus\StaffTicketProcessing\Http\Middleware\EnsureTicketOperator;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/staff')->name('api.v1.staff.')
    ->middleware(['auth:sanctum', SetLocale::class])->group(function (): void {
        Route::get('/sale-context', [CounterSaleController::class, 'context'])->middleware(EnsureTicketOperator::class)->name('sale-context.show');
        Route::get('/tickets', TicketListController::class)->middleware(EnsureTicketOperator::class.':ticket.view')->name('tickets.index');
        Route::middleware(EnsureTicketOperator::class.':booking.create')->group(function (): void {
            Route::get('/trips', [CounterSaleController::class, 'trips'])->middleware('throttle:staff-ticket-trips')->name('trips.index');
            Route::get('/trips/{trip}/booking-context', [CounterSaleController::class, 'booking'])->whereNumber('trip')->middleware('throttle:staff-ticket-booking')->name('trips.booking-context.show');
            Route::post('/sales', [CounterSaleController::class, 'store'])->middleware('throttle:staff-ticket-sales')->name('sales.store');
            Route::post('/previews', [CounterSaleController::class, 'preview'])->middleware('throttle:staff-ticket-previews')->name('previews.store');
            Route::post('/quotes', [CounterSaleController::class, 'quote'])->middleware('throttle:staff-ticket-quotes')->name('quotes.store');
            Route::get('/sales/{sale}', [CounterSaleController::class, 'show'])->whereUuid('sale')->name('sales.show');
            Route::delete('/sales/{sale}', [CounterSaleController::class, 'destroy'])->whereUuid('sale')->name('sales.destroy');
            Route::post('/sales/{sale}/cash-collections', [CounterSaleController::class, 'cash'])->whereUuid('sale')->middleware('throttle:staff-ticket-sales')->name('sales.cash-collections.store');
        });
    });
