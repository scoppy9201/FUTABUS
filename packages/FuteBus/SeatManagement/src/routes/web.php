<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/SeatManagement', function () {
        return view('SeatManagement::index');
    })->name('SeatManagement.index');
});
