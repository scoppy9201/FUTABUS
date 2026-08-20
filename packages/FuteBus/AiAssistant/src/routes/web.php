<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/AiAssistant', function () {
        return view('AiAssistant::index');
    })->name('AiAssistant.index');
});
