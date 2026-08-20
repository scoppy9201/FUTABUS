<?php

declare(strict_types=1);

namespace FuteBus\Payment\Providers;

use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'Payment');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'Payment');
    }
}