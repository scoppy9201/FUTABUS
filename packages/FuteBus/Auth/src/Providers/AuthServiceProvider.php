<?php

declare(strict_types=1);

namespace FuteBus\Auth\Providers;

use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'Auth');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'Auth');
    }
}