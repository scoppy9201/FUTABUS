<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/Cancellation', function () {
        return view('Cancellation::index');
    })->name('Cancellation.index');
});
