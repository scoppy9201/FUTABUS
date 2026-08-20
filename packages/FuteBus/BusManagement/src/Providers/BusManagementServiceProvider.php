<?php

declare(strict_types=1);

namespace FuteBus\BusManagement\Providers;

use Illuminate\Support\ServiceProvider;

class BusManagementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'BusManagement');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'BusManagement');
    }
}