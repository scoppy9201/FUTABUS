<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/BookingManagement', function () {
        return view('BookingManagement::index');
    })->name('BookingManagement.index');
});
