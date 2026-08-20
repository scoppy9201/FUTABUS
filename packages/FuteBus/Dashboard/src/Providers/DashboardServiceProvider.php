<?php

declare(strict_types=1);

namespace FuteBus\Dashboard\Providers;

use Illuminate\Support\ServiceProvider;

class DashboardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'Dashboard');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'Dashboard');
    }
}