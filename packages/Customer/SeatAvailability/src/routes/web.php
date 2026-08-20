<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/SeatAvailability', function () {
        return view('SeatAvailability::index');
    })->name('SeatAvailability.index');
});
