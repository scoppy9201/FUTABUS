<?php

declare(strict_types=1);

use FuteBus\Core\Http\Controllers\HomeController;
use FuteBus\Core\Http\Controllers\TicketLookupController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/huong-dan-dat-ve-tren-web', [HomeController::class, 'bookingGuide'])->name('booking-guide');
Route::get('/ve-chung-toi', [HomeController::class, 'about'])->name('about');
Route::get('/chinh-sach/chinh-sach-bao-mat', [HomeController::class, 'privacy'])->name('privacy');
Route::get('/chinh-sach/chinh-sach-thanh-toan', [HomeController::class, 'payment'])->name('payment');
Route::get('/chinh-sach/chinh-sach-gia', [HomeController::class, 'pricing'])->name('pricing');
Route::get('/chinh-sach/chinh-sach-doi-ve-hoan-tien', [HomeController::class, 'refund'])->name('refund');
Route::get('/tra-cuu-ve', [TicketLookupController::class, 'index'])->name('ticket-lookup');
Route::post('/tra-cuu-ve', [TicketLookupController::class, 'search'])
    ->middleware('throttle:10,1')
    ->name('ticket-lookup.search');
Route::get('/hoa-don', [HomeController::class, 'invoice'])->name('invoice');
Route::get('/dieu-khoan-su-dung', [HomeController::class, 'terms'])->name('terms');
Route::get('/chinh-sach/dieu-kien-giao-dich-chung', [HomeController::class, 'transactionConditions'])
    ->name('transaction-conditions');
Route::get('/chinh-sach/dieu-kien-cung-cap-dich-vu', [HomeController::class, 'serviceConditions'])
    ->name('service-conditions');
Route::get('/hoi-dap', [HomeController::class, 'faq'])->name('faq');
Route::get('/hoi-dap/{category:slug}', [HomeController::class, 'faqCategory'])->name('faq-category');
Route::get('/chinh-sach/giai-quyet-khieu-nai', [HomeController::class, 'complaint'])->name('complaint');
Route::get('/chinh-sach/ho-tro-khach-hang', [HomeController::class, 'customerSupport'])->name('customer-support');
Route::get('/danh-sach-chi-nhanh', [HomeController::class, 'branches'])->name('branches');
Route::get('/lich-trinh', [HomeController::class, 'schedules'])->name('schedules');
Route::get('/tin-tuc', [HomeController::class, 'news'])->name('news');
Route::get('/lien-he', [HomeController::class, 'contact'])->name('contact');
Route::post('/lien-he', [HomeController::class, 'submitContact'])->name('contact.submit');
