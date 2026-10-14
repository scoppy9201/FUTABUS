<?php

declare(strict_types=1);

namespace FuteBus\TicketManagement\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class TicketManagementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Route::prefix('api')->middleware('api')->group(__DIR__.'/../routes/api.php');
        Route::middleware('web')->group(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'TicketManagement');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'TicketManagement');
    }
}
