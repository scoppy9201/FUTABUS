<?php

declare(strict_types=1);

namespace FuteBus\CustomerManagement\Providers;

use Illuminate\Support\ServiceProvider;

class CustomerManagementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'CustomerManagement');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'CustomerManagement');
    }
}