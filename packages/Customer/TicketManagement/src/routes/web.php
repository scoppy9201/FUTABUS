<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/TicketManagement', function () {
        return view('TicketManagement::index');
    })->name('TicketManagement.index');
});
