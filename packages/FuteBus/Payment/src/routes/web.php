<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/Payment', function () {
        return view('Payment::index');
    })->name('Payment.index');
});
