<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/SystemSetting', function () {
        return view('SystemSetting::index');
    })->name('SystemSetting.index');
});
