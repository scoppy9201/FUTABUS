<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/BusManagement', function () {
        return view('BusManagement::index');
    })->name('BusManagement.index');
});
