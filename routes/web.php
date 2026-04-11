<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\LoginController;

/*
| Web Routes
|
| File này chỉ có nhiệm vụ serve Blade views.
| Toàn bộ data/logic xử lý qua /api/v1/... trong api.php.
|
*/

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/dashboard');
    }
    return view('welcome');
});

/*
| AUTH VIEWS (public — chưa đăng nhập)
*/
Route::middleware('guest')->group(function () {
    Route::get('/login',           fn() => view('auth.AuthForm'))       ->name('login');
    Route::get('/register',        fn() => view('auth.AuthForm'))       ->name('register');
    Route::get('/forgot-password', fn() => view('auth.forgot-password'))->name('password.request');
    Route::get('/verify-code',     fn() => view('auth.verify-code'))    ->name('password.verify.form');
    Route::get('/reset-password',  fn() => view('auth.reset-password')) ->name('password.reset.form');
});

Route::get('/auth/google',          [LoginController::class, 'redirectToGoogle'])    ->name('google.redirect');
Route::get('/auth/google/callback', [LoginController::class, 'handleGoogleCallback'])->name('google.callback');

/*
| AUTHENTICATED VIEWS (cần đăng nhập)
*/
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', fn() => view('dashboard')) ->name('dashboard');
    // Transactions
    Route::get('/transactions', fn() => view('transactions.index')) ->name('transactions.index');
    // Categories
    Route::get('/categories', fn() => view('categories.index'))->name('categories.index');
    // Budgets
    Route::get('/budgets', fn() => view('budgets.index'))->name('budgets.index');
    // Profile
    Route::get('/profile', fn() => view('profile.show'))->name('profile.show');
    // Change password
    Route::get('/change-password', fn() => view('change-password'))->name('change-password.form');
    // Settings
    Route::get('/settings', fn() => view('settings')) ->name('settings.index');
    // Groups (split bill)
    Route::get('/groups', fn() => view('groups'))->name('groups.index');
    // Money wallets
    Route::get('/money-wallets', fn() => view('money-wallets')) ->name('money-wallets.index');
    // Notifications
    Route::get('/notifications', fn() => view('notifications')) ->name('notifications.index');
    // AI Assistant
    Route::get('/ai-assistant', fn() => view('ai-assistant')) ->name('ai-assistant.index');
    // Currency
    Route::get('/currency', fn() => view('currency.index')) ->name('currency.index');
    // Search
    Route::get('/search', fn() => view('search')) ->name('search');
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
