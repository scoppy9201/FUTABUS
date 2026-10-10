<?php

declare(strict_types=1);

use FuteBus\Core\Http\Controllers\Api\ContactController;
use FuteBus\Core\Http\Controllers\Api\PublicContentController;
use FuteBus\Core\Http\Controllers\Api\TicketLookupController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::get('/news', [PublicContentController::class, 'news'])->name('news.index');
    Route::get('/news/{slug}', [PublicContentController::class, 'article'])->name('news.show');
    Route::get('/faq-categories', [PublicContentController::class, 'faqCategories'])->name('faq-categories.index');
    Route::get('/faq-categories/{category:slug}', [PublicContentController::class, 'faqCategory'])
        ->name('faq-categories.show');
    Route::get('/branches', [PublicContentController::class, 'branches'])->name('branches.index');
    Route::get('/schedules', [PublicContentController::class, 'schedules'])->name('schedules.index');
    Route::post('/contact-messages', [ContactController::class, 'store'])
        ->middleware('throttle:5,1')->name('contact-messages.store');
    Route::post('/ticket-lookups', TicketLookupController::class)
        ->middleware('throttle:10,1')->name('ticket-lookups.store');
});
