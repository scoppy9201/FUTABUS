<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/Dashboard', function () {
        return view('Dashboard::index');
    })->name('Dashboard.index');
});
