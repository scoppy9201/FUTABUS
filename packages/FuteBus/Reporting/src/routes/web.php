<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/Reporting', function () {
        return view('Reporting::index');
    })->name('Reporting.index');
});
