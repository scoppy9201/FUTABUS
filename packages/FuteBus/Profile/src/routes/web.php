<?php

declare(strict_types=1);

use FuteBus\Profile\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/thong-tin-tai-khoan/thong-tin-chung', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/thong-tin-tai-khoan/thong-tin-chung', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/thong-tin-tai-khoan/anh-dai-dien', [ProfileController::class, 'avatar'])->name('profile.avatar');
});
