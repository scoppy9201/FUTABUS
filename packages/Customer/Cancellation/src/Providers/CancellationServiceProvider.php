<?php

declare(strict_types=1);

namespace FuteBus\Cancellation\Providers;

use Illuminate\Support\ServiceProvider;

class CancellationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'Cancellation');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'Cancellation');
    }
}