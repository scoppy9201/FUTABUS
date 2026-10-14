<?php

declare(strict_types=1);

use FuteBus\Notification\Http\Controllers\NotificationInboxController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('thong-bao')->name('notifications.')->group(function (): void {
    Route::get('/', [NotificationInboxController::class, 'index'])->name('index');
    Route::post('/{notification}/da-doc', [NotificationInboxController::class, 'read'])->name('read');
});
