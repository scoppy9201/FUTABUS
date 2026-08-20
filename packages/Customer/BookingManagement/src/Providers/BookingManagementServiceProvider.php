<?php

declare(strict_types=1);

namespace FuteBus\BookingManagement\Providers;

use Illuminate\Support\ServiceProvider;

class BookingManagementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'BookingManagement');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'BookingManagement');
    }
}