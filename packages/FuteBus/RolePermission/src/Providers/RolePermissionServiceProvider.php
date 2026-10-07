<?php

declare(strict_types=1);

namespace FuteBus\RolePermission\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RolePermissionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'RolePermission');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'RolePermission');
        Route::middleware('web')->group(__DIR__.'/../routes/web.php');
    }
}
