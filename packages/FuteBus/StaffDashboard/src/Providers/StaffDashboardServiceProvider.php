<?php

declare(strict_types=1);

namespace FuteBus\StaffDashboard\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class StaffDashboardServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'StaffDashboard');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'StaffDashboard');
        Route::middleware('web')->group(__DIR__.'/../routes/web.php');
        Route::middleware('api')->prefix('api')->group(__DIR__.'/../routes/api.php');
    }
}
