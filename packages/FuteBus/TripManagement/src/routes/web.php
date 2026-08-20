<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/TripManagement', function () {
        return view('TripManagement::index');
    })->name('TripManagement.index');
});
