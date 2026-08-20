<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/Auth', function () {
        return view('Auth::index');
    })->name('Auth.index');
});
