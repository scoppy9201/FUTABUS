<?php

declare(strict_types=1);

use FuteBus\Auth\Http\Middleware\EnsureApiToken;
use FuteBus\Payment\Http\Controllers\Api\PaymentIntentController;
use FuteBus\Payment\Http\Controllers\SePayWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/sepay/webhook', SePayWebhookController::class)->name('sepay.webhook');

Route::prefix('v1')->name('api.v1.')->middleware(['auth:sanctum', EnsureApiToken::class])->group(function (): void {
    Route::post('/trips/{trip}/payment-intents', [PaymentIntentController::class, 'store'])
        ->middleware('throttle:10,1')->whereNumber('trip')->name('payment-intents.store');
    Route::get('/payment-intents/{intent}', [PaymentIntentController::class, 'show'])
        ->name('payment-intents.show');
    Route::delete('/payment-intents/{intent}', [PaymentIntentController::class, 'destroy'])
        ->name('payment-intents.destroy');
});
