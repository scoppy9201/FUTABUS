<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

// Trang chủ
Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/dashboard');
    }
    return view('welcome');
});

// Auth views 
Route::get('/login',    fn() => view('auth.AuthForm'))->name('login');
Route::get('/register', fn() => view('auth.AuthForm'))->name('register');

Route::get('/forgot-password', fn() => view('auth.forgot-password'))->name('password.request');
Route::get('/verify-code',     fn() => view('auth.verify-code'))    ->name('password.verify.form');
Route::get('/reset-password',  fn() => view('auth.reset-password')) ->name('password.reset.form');

// Google OAuth callback 
Route::get('/auth/google',          [\App\Http\Controllers\LoginController::class, 'redirectToGoogle'])    ->name('google.redirect');
Route::get('/auth/google/callback', [\App\Http\Controllers\LoginController::class, 'handleGoogleCallback'])->name('google.callback');

// ← THÊM MỚI: Sync session sau khi login bằng API token
Route::post('/auth/sync-session', function (Request $request) {
    $token = PersonalAccessToken::findToken($request->input('token'));

    if (!$token) {
        return response()->json(['message' => 'Token không hợp lệ'], 401);
    }

    $user = $token->tokenable;
    Auth::login($user);
    $request->session()->regenerate();

    return response()->json(['message' => 'Session đã được đồng bộ']);
})->name('auth.sync-session');

// Dashboard
Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard')->middleware('auth');

// App views 
Route::middleware('auth')->group(function () {
    Route::get('/transactions',     fn() => view('transactions'))    ->name('transactions.index');
    Route::get('/categories',       fn() => view('categories'))      ->name('categories.index');
    Route::get('/budgets',          fn() => view('budgets'))         ->name('wallets.index');
    Route::get('/profile',          fn() => view('profile'))         ->name('profile.show');
    Route::get('/settings',         fn() => view('settings'))        ->name('settings.index');
    Route::get('/groups',           fn() => view('groups'))          ->name('groups.index');
    Route::get('/money-wallets',    fn() => view('money-wallets'))   ->name('money-wallets.index');
    Route::get('/notifications',    fn() => view('notifications'))   ->name('notifications.index');
    Route::get('/ai-assistant',     fn() => view('ai-assistant'))    ->name('ai-assistant.index');
    Route::get('/currency',         fn() => view('currency'))        ->name('currency.index');
    Route::get('/search',           fn() => view('search'))          ->name('search');

    // change password view
    Route::get('/change-password',  fn() => view('auth.change-password'))->name('change-password.form');

    // Logout
    Route::post('/logout', [\App\Http\Controllers\LoginController::class, 'logout'])->name('logout');
});
