<?php

declare(strict_types=1);

use FuteBus\RouteManagement\Http\Controllers\RouteController;
use FuteBus\RouteManagement\Http\Controllers\RouteStopController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('quan-tri/tuyen-duong')->name('route-management.')->group(function () {
    Route::get('/', [RouteController::class, 'index'])->middleware('permission:route.view')->name('index');
    Route::get('/diem-dung', [RouteStopController::class, 'index'])->middleware('permission:route.view')->name('stops.index');
    Route::post('/', [RouteController::class, 'store'])->middleware('permission:route.create')->name('store');
    Route::put('/{route}', [RouteController::class, 'update'])->whereNumber('route')->middleware('permission:route.update')->name('update');
    Route::delete('/{route}', [RouteController::class, 'destroy'])->whereNumber('route')->middleware('permission:route.delete')->name('destroy');
    Route::post('/{route}/diem-dung', [RouteStopController::class, 'store'])->whereNumber('route')->middleware('permission:route.update')->name('stops.store');
    Route::put('/{route}/diem-dung/{stop}', [RouteStopController::class, 'update'])->whereNumber(['route', 'stop'])->middleware('permission:route.update')->name('stops.update');
    Route::delete('/{route}/diem-dung/{stop}', [RouteStopController::class, 'destroy'])->whereNumber(['route', 'stop'])->middleware('permission:route.update')->name('stops.destroy');
});
