<?php

declare(strict_types=1);

namespace FuteBus\SeatManagement\Providers;

use Illuminate\Support\ServiceProvider;

class SeatManagementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'SeatManagement');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'SeatManagement');
    }
}