<?php

declare(strict_types=1);

namespace FuteBus\SeatAvailability\Providers;

use Illuminate\Support\ServiceProvider;

class SeatAvailabilityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'SeatAvailability');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'SeatAvailability');
    }
}