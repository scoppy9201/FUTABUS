<?php

declare(strict_types=1);

use FuteBus\UserManagement\Http\Controllers\StaffAccountController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('quan-tri/tai-khoan-nhan-vien')->name('staff-accounts.')->group(function (): void {
    Route::get('/', [StaffAccountController::class, 'index'])->middleware('permission:user.view')->name('index');
    Route::get('/tao-moi', [StaffAccountController::class, 'create'])->middleware('permission:user.create')->name('create');
    Route::post('/', [StaffAccountController::class, 'store'])->middleware('permission:user.create')->name('store');
    Route::get('/{staff}', [StaffAccountController::class, 'show'])->whereNumber('staff')->middleware('permission:user.view')->name('show');
    Route::get('/{staff}/chinh-sua', [StaffAccountController::class, 'edit'])->whereNumber('staff')->middleware('permission:user.update')->name('edit');
    Route::put('/{staff}', [StaffAccountController::class, 'update'])->whereNumber('staff')->middleware('permission:user.update')->name('update');
    Route::patch('/{staff}/trang-thai', [StaffAccountController::class, 'toggleStatus'])->whereNumber('staff')->middleware('permission:user.update')->name('status');
    Route::delete('/{staff}', [StaffAccountController::class, 'destroy'])->whereNumber('staff')->middleware('permission:user.delete')->name('destroy');
});

Route::redirect('/UserManagement', '/quan-tri/tai-khoan-nhan-vien')
    ->middleware('auth')
    ->name('UserManagement.index');
