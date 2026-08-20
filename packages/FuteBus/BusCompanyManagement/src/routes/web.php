<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/BusCompanyManagement', function () {
        return view('BusCompanyManagement::index');
    })->name('BusCompanyManagement.index');
});
