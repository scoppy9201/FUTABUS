<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/CustomerManagement', function () {
        return view('CustomerManagement::index');
    })->name('CustomerManagement.index');
});
