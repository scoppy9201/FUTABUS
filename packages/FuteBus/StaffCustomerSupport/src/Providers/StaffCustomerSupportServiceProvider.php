<?php

declare(strict_types=1);

namespace FuteBus\StaffCustomerSupport\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class StaffCustomerSupportServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'StaffCustomerSupport');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'StaffCustomerSupport');
        Route::middleware('web')->group(__DIR__.'/../routes/web.php');
        Route::middleware('api')->prefix('api')->group(__DIR__.'/../routes/api.php');
    }
}
