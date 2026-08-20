<?php

declare(strict_types=1);

namespace FuteBus\AiAssistant\Providers;

use Illuminate\Support\ServiceProvider;

class AiAssistantServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'AiAssistant');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'AiAssistant');
    }
}