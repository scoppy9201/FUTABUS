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
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\SplitGroupController;
use App\Http\Controllers\GroupMemberController;
use App\Http\Controllers\GroupBalanceController;
use App\Http\Controllers\GroupExpenseController;
use App\Http\Controllers\GroupDebtController;

use App\Http\Controllers\NotificationController;

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

    // Search toàn hệ thống
    Route::get('/search', [SearchController::class, 'index'])->name('search');

    // Quy đổi tỷ giá tiền tệ
    Route::get('/currency', [CurrencyController::class, 'index'])->name('currency.index');

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

    Route::prefix('groups')->name('groups.')->group(function () {

        // ── STATIC routes: PHẢI đặt trước /{group} ──────────────
        Route::get('/',  [SplitGroupController::class, 'index'])->name('index');
        Route::post('/', [SplitGroupController::class, 'store'])->name('store');

        // Tìm kiếm user để mời (AJAX) — đặt trước /{group}
        Route::get('/search-users', [SplitGroupController::class, 'searchUsers'])->name('search-users');

        // Token invitation — đặt trước /{group}
        Route::get('/invitations/{token}/accept',  [GroupMemberController::class, 'accept'])->name('invite.accept');
        Route::get('/invitations/{token}/decline', [GroupMemberController::class, 'decline'])->name('invite.decline');

        // ── DYNAMIC routes /{group} ──────────────────────────────
        Route::get('/{group}',    [SplitGroupController::class, 'show'])   ->name('show');
        Route::put('/{group}',    [SplitGroupController::class, 'update']) ->name('update');
        Route::delete('/{group}', [SplitGroupController::class, 'destroy'])->name('destroy');

        Route::post('/{group}/toggle-balance-visibility', [SplitGroupController::class, 'toggleBalanceVisibility'])->name('toggle-visibility');

        Route::post('/{group}/invite',                   [GroupMemberController::class, 'invite'])  ->name('invite');
        Route::post('/{group}/leave',                    [GroupMemberController::class, 'leave'])   ->name('leave');
        Route::delete('/{group}/members/{member}',       [GroupMemberController::class, 'remove'])  ->name('members.remove');
        Route::post('/{group}/members/{member}/promote', [GroupMemberController::class, 'promote']) ->name('members.promote');
        Route::post('/{group}/members/{member}/demote',  [GroupMemberController::class, 'demote'])  ->name('members.demote');

        Route::prefix('/{group}/balance')->name('balance.')->group(function () {
            Route::get('/',                              [GroupBalanceController::class, 'index'])  ->name('index');
            Route::post('/proposals',                    [GroupBalanceController::class, 'propose'])->name('propose');
            Route::post('/proposals/{proposal}/approve', [GroupBalanceController::class, 'approve'])->name('approve');
            Route::post('/proposals/{proposal}/reject',  [GroupBalanceController::class, 'reject']) ->name('reject');
            Route::post('/proposals/{proposal}/cancel',  [GroupBalanceController::class, 'cancel']) ->name('cancel');
        });

        Route::prefix('/{group}/expenses')->name('expense.')->group(function () {
            Route::get('/',                              [GroupExpenseController::class, 'index'])  ->name('index');
            Route::post('/',                             [GroupExpenseController::class, 'store'])  ->name('store');
            Route::post('/proposals/{proposal}/approve', [GroupExpenseController::class, 'approve'])->name('approve');
            Route::post('/proposals/{proposal}/reject',  [GroupExpenseController::class, 'reject']) ->name('reject');
            Route::post('/proposals/{proposal}/cancel',  [GroupExpenseController::class, 'cancel']) ->name('cancel');
        });

        Route::prefix('/{group}/debts')->name('debt.')->group(function () {
            Route::post('/',             [GroupDebtController::class, 'store'])  ->name('store');
            Route::get('/summary',       [GroupDebtController::class, 'summary'])->name('summary');
            Route::post('/{debt}/settle',[GroupDebtController::class, 'settle']) ->name('settle');
        });
    });

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/',           [NotificationController::class, 'index'])      ->name('index');
        Route::get('/dropdown',   [NotificationController::class, 'dropdown'])   ->name('dropdown');
        Route::get('/by-date',    [NotificationController::class, 'byDate'])     ->name('by-date');
        Route::post('/mark-read/{notification}', [NotificationController::class, 'markRead'])->name('mark-read');
        Route::post('/mark-all-read',            [NotificationController::class, 'markAllRead'])->name('mark-all-read');
        Route::get('/badge',      [NotificationController::class, 'badge'])      ->name('badge');
    });


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
