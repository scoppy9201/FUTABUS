<?php

declare(strict_types=1);

use FuteBus\AiAssistant\Http\Controllers\Api\ChatbotController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::post('/chatbot/messages', ChatbotController::class)
        ->middleware('throttle:20,1')->name('chatbot.messages.store');
});
