<?php

declare(strict_types=1);

namespace FuteBus\TripManagement\Providers;

use FuteBus\TripManagement\Console\GenerateTripsCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class TripManagementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'TripManagement');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'TripManagement');
        Route::middleware('web')->group(__DIR__ . '/../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->commands([GenerateTripsCommand::class]);
        }

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('trips:generate')->dailyAt('00:00');
        });
    }
}