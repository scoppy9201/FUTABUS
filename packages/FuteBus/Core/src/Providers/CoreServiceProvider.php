<?php

declare(strict_types=1);

namespace FuteBus\Core\Providers;

use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'Core');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'Core');
    }
}