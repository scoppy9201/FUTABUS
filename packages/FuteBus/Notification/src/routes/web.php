<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/Notification', function () {
        return view('Notification::index');
    })->name('Notification.index');
});
