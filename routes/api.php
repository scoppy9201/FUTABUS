<?php

use Illuminate\Support\Facades\Route;

/*
| API Routes — prefix /api/v1
|
| Các route nghiệp vụ đặt vé được đăng ký từng module qua ServiceProvider
| của package tương ứng (xem packages/).
|
*/

Route::prefix('v1')->name('api.')->group(function () {
    Route::get('/health', fn () => response()->json(['status' => 'ok']));
});