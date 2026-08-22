<?php

declare(strict_types=1);

use FuteBus\Core\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/ve-chung-toi', [HomeController::class, 'about'])->name('about');
