<?php

declare(strict_types=1);

use FuteBus\RolePermission\Http\Controllers\AccessControlController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('quan-tri/quyen-truy-cap')->name('access-management.')->group(function (): void {
    Route::get('/', [AccessControlController::class, 'index'])->name('index');
    Route::get('/danh-muc-chuc-nang', [AccessControlController::class, 'catalog'])->name('catalog');
    Route::post('/danh-muc-chuc-nang', [AccessControlController::class, 'storePermission'])->name('catalog.store');
    Route::put('/danh-muc-chuc-nang/{permission}', [AccessControlController::class, 'updatePermission'])->whereNumber('permission')->name('catalog.update');
    Route::patch('/danh-muc-chuc-nang/{permission}/trang-thai', [AccessControlController::class, 'setPermissionStatus'])->whereNumber('permission')->name('catalog.status');
    Route::get('/nhan-vien', [AccessControlController::class, 'users'])->name('users');
    Route::put('/nhan-vien/{staff}/vai-tro', [AccessControlController::class, 'assignRoles'])->whereNumber('staff')->name('users.assign');

    Route::get('/vai-tro/tao-moi', [AccessControlController::class, 'createRole'])->name('roles.create');
    Route::post('/vai-tro', [AccessControlController::class, 'storeRole'])->name('roles.store');
    Route::get('/vai-tro/{role}/chinh-sua', [AccessControlController::class, 'editRole'])->whereNumber('role')->name('roles.edit');
    Route::put('/vai-tro/{role}', [AccessControlController::class, 'updateRole'])->whereNumber('role')->name('roles.update');
    Route::delete('/vai-tro/{role}', [AccessControlController::class, 'deleteRole'])->whereNumber('role')->name('roles.delete');
});

Route::redirect('/RolePermission', '/quan-tri/quyen-truy-cap')
    ->middleware('auth')
    ->name('RolePermission.index');
