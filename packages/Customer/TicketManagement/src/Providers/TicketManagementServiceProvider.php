<?php

declare(strict_types=1);

namespace FuteBus\TicketManagement\Providers;

use Illuminate\Support\ServiceProvider;

class TicketManagementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'TicketManagement');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'TicketManagement');
    }
}