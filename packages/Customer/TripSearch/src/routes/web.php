<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/TripSearch', function () {
        return view('TripSearch::index');
    })->name('TripSearch.index');
});
