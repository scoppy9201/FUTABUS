<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/UserManagement', function () {
        return view('UserManagement::index');
    })->name('UserManagement.index');
});
