<?php

declare(strict_types=1);

namespace FuteBus\Profile\Providers;

use Illuminate\Support\ServiceProvider;

class ProfileServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'Profile');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'Profile');
    }
}