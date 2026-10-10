<?php

declare(strict_types=1);

namespace FuteBus\TripSearch\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class TripSearchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'TripSearch');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'TripSearch');
        Route::prefix('api')->middleware('api')->group(__DIR__.'/../routes/api.php');
    }
}
