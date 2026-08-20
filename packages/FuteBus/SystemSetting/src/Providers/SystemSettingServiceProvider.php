<?php

declare(strict_types=1);

namespace FuteBus\SystemSetting\Providers;

use Illuminate\Support\ServiceProvider;

class SystemSettingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'SystemSetting');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'SystemSetting');
    }
}