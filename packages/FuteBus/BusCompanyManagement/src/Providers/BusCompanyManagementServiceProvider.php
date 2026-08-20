<?php

declare(strict_types=1);

namespace FuteBus\BusCompanyManagement\Providers;

use Illuminate\Support\ServiceProvider;

class BusCompanyManagementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'BusCompanyManagement');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'BusCompanyManagement');
    }
}