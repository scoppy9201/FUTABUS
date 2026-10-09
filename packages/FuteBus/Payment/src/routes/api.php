<?php

declare(strict_types=1);

use FuteBus\Payment\Http\Controllers\SePayWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/sepay/webhook', SePayWebhookController::class)->name('sepay.webhook');
