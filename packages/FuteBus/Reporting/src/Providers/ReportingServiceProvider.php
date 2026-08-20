<?php

declare(strict_types=1);

namespace FuteBus\Reporting\Providers;

use Illuminate\Support\ServiceProvider;

class ReportingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'Reporting');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'Reporting');
    }
}