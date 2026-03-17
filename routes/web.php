<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AIAssistantController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    Route::get('/auth/google', [LoginController::class, 'redirectToGoogle'])->name('google.redirect');
    Route::get('/auth/google/callback', [LoginController::class, 'handleGoogleCallback']);

    // Forgot password flow
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetCode'])->name('password.email');
    Route::get('/verify-code', [ForgotPasswordController::class, 'showVerifyForm'])->name('password.verify.form');
    Route::post('/verify-code', [ForgotPasswordController::class, 'verifyCode'])->name('password.verify');
    Route::get('/reset-password', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset.form');
    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.update');
});

// Authenticated routes
Route::middleware('auth')->group(function () {

    // Dashboard 
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::match(['put', 'patch'], '/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');

    // Change password
    Route::get('/change-password', [ChangePasswordController::class, 'showChangeForm'])->name('change-password.form');
    Route::post('/change-password', [ChangePasswordController::class, 'changePassword'])->name('change-password.update');

    // Quản lý danh mục chi tiêu
    Route::resource('categories', CategoryController::class)->parameters(['categories' => 'category']);
    Route::post('/categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');

    // Quản lý ngân sách
    Route::resource('wallets', WalletController::class)->parameters(['wallets' => 'wallet']);
    Route::post('/wallets/{wallet}/toggle-status', [WalletController::class, 'toggleStatus'])->name('wallets.toggle-status');
    Route::post('/wallets/{wallet}/sync-balance', [WalletController::class, 'syncBalance'])->name('wallets.sync-balance');

    // Quản lý giao dịch
    Route::resource('transactions', TransactionController::class)->parameters(['transactions' => 'transaction']);

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// AI Assistant Routes
Route::prefix('ai-assistant')->middleware('auth')->group(function () {
    Route::get('/', [AIAssistantController::class, 'index'])->name('ai-assistant.index');
    Route::post('/chat', [AIAssistantController::class, 'chat'])->name('ai-assistant.chat');
    Route::post('/analyze', [AIAssistantController::class, 'analyze'])->name('ai-assistant.analyze');
    Route::post('/ai/clear', [AIAssistantController::class, 'clearHistory'])->name('ai.clear');
    Route::get('/suggestions', [AIAssistantController::class, 'suggestions'])->name('ai-assistant.suggestions');
    Route::get('/insights', [AIAssistantController::class, 'insights'])->name('ai-assistant.insights');
});

Route::redirect('/home', '/dashboard')->middleware('auth');