<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/Core', function () {
        return view('Core::index');
    })->name('Core.index');
});
