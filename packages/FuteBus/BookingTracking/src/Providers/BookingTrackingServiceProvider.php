<?php

declare(strict_types=1);

namespace FuteBus\BookingTracking\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class BookingTrackingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'BookingTracking');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'BookingTracking');
        Route::middleware('web')->group(__DIR__.'/../routes/web.php');
    }
}
