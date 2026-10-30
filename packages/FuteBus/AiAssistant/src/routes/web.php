<?php

declare(strict_types=1);

use FuteBus\AiAssistant\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::post('/tro-ly/hoi', ChatController::class)
    ->middleware('throttle:30,1')
    ->name('ai-assistant.ask');
