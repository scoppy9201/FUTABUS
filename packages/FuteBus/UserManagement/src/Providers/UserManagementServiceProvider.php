<?php

declare(strict_types=1);

namespace FuteBus\UserManagement\Providers;

use Illuminate\Support\ServiceProvider;

class UserManagementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'UserManagement');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'UserManagement');
    }
}