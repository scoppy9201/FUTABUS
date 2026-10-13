<?php

declare(strict_types=1);

namespace Staff\TicketProcessing\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class TicketProcessingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        foreach (['staff-ticket-trips' => 30, 'staff-ticket-booking' => 30, 'staff-ticket-sales' => 10, 'staff-ticket-previews' => 30, 'staff-ticket-quotes' => 60] as $name => $attempts) {
            RateLimiter::for($name, fn (Request $request) => Limit::perMinute($attempts)->by((string) ($request->user()?->id ?? $request->ip())));
        }
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'StaffTickets');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'StaffTickets');
        Route::middleware('web')->group(__DIR__.'/../routes/web.php');
        Route::middleware('api')->prefix('api')->group(__DIR__.'/../routes/api.php');
    }
}
