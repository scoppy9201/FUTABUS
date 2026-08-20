<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
| Web Routes
|
| Các route nền tảng tối giản. Logic chi tiết sẽ được đăng ký từng module
| qua ServiceProvider tương ứng (xem packages/).
|
*/

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/dashboard');
    }
    return view('welcome');
});

Route::get('/login', fn () => view('auth.login'))->name('login');
Route::get('/register', fn () => view('auth.register'))->name('register');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    Route::post('/logout', function () {
        Auth::logout();
        return redirect()->route('login');
    })->name('logout');
});