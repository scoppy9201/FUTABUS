<?php

declare(strict_types=1);

use FuteBus\TicketManagement\Http\Controllers\TicketLookupController;
use Illuminate\Support\Facades\Route;

Route::get('/tra-cuu-ve', [TicketLookupController::class, 'index'])->name('ticket-lookup');
Route::post('/tra-cuu-ve', [TicketLookupController::class, 'search'])
    ->middleware('throttle:10,1')
    ->name('ticket-lookup.search');
