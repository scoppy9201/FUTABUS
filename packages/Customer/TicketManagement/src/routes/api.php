<?php

declare(strict_types=1);

use FuteBus\TicketManagement\Http\Controllers\Api\TicketLookupController;
use Illuminate\Support\Facades\Route;

Route::post('/v1/ticket-lookups', TicketLookupController::class)
    ->middleware('throttle:10,1')->name('api.v1.ticket-lookups.store');
