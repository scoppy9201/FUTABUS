<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/RolePermission', function () {
        return view('RolePermission::index');
    })->name('RolePermission.index');
});
