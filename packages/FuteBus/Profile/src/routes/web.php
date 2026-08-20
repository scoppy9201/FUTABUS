<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/Profile', function () {
        return view('Profile::index');
    })->name('Profile.index');
});
