<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/RouteManagement', function () {
        return view('RouteManagement::index');
    })->name('RouteManagement.index');
});
