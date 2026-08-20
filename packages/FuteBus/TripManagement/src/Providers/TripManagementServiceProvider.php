<?php

declare(strict_types=1);

namespace FuteBus\TripManagement\Providers;

use Illuminate\Support\ServiceProvider;

class TripManagementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'TripManagement');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'TripManagement');
    }
}