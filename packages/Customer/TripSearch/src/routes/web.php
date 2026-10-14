<?php

declare(strict_types=1);

use FuteBus\TripSearch\Http\Controllers\TripSearchController;
use Illuminate\Support\Facades\Route;

Route::get('/dat-ve', TripSearchController::class)->name('trip-search');
