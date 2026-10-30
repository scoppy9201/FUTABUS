<?php

declare(strict_types=1);

namespace FuteBus\Notification\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'Notification');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'Notification');
        Route::middleware('web')->group(__DIR__.'/../routes/web.php');
    }
}
