<?php

declare(strict_types=1);

use FuteBus\BookingManagement\Http\Controllers\TripBookingController;
use Illuminate\Support\Facades\Route;

Route::get('/dat-ve/chon-chuyen/{trip}', TripBookingController::class)->whereNumber('trip')->name('trip-booking.show');
