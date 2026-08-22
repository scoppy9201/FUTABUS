<?php

declare(strict_types=1);

use FuteBus\Core\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/ve-chung-toi', [HomeController::class, 'about'])->name('about');
Route::get('/chinh-sach/chinh-sach-bao-mat', [HomeController::class, 'privacy'])->name('privacy');
Route::get('/chinh-sach/chinh-sach-thanh-toan', [HomeController::class, 'payment'])->name('payment');
Route::get('/chinh-sach/chinh-sach-gia', [HomeController::class, 'pricing'])->name('pricing');
Route::get('/chinh-sach/chinh-sach-doi-ve-hoan-tien', [HomeController::class, 'refund'])->name('refund');
Route::get('/tra-cuu-ve', [HomeController::class, 'ticketLookup'])->name('ticket-lookup');
Route::get('/dieu-khoan-su-dung', [HomeController::class, 'terms'])->name('terms');
Route::get('/chinh-sach/dieu-kien-giao-dich-chung', [HomeController::class, 'transactionConditions'])
    ->name('transaction-conditions');
Route::get('/chinh-sach/dieu-kien-cung-cap-dich-vu', [HomeController::class, 'serviceConditions'])
    ->name('service-conditions');
