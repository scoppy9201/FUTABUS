<?php

namespace Tests\Feature\Architecture;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class ModuleBoundariesTest extends TestCase
{
    public function test_controllers_use_request_contracts_and_do_not_call_other_controllers(): void
    {
        foreach ($this->controllers() as $path) {
            $source = file_get_contents($path);
            $this->assertDoesNotMatchRegularExpression('/->validate\s*\(|Validator::make\s*\(/', $source, $path);
            $this->assertDoesNotMatchRegularExpression('/^use FuteBus\\\\[^;]+\\\\Http\\\\Controllers\\\\/m', $source, $path);
        }
    }

    public function test_domain_entrypoints_are_owned_and_registered_by_their_modules(): void
    {
        foreach ([
            'trip-search'          => 'FuteBus\\TripSearch\\Http\\Controllers\\TripSearchController',
            'trip-booking.show'    => 'FuteBus\\BookingManagement\\Http\\Controllers\\TripBookingController',
            'ticket-lookup'        => 'FuteBus\\TicketManagement\\Http\\Controllers\\TicketLookupController',
            'bookings.index'       => 'FuteBus\\BookingTracking\\Http\\Controllers\\BookingMonitorController',
            'staff.bookings.index' => 'FuteBus\\BookingTracking\\Http\\Controllers\\BookingMonitorController',
        ] as $name => $controller) {
            $route = app('router')->getRoutes()->getByName($name);
            $this->assertNotNull($route, $name);
            $this->assertStringStartsWith($controller, $route->getActionName());
        }
        $root = json_decode(file_get_contents(base_path('composer.json')), true, flags: JSON_THROW_ON_ERROR);
        $module = json_decode(file_get_contents(base_path('packages/FuteBus/BookingTracking/composer.json')), true, flags: JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey($module['name'], $root['require']);
        $this->assertSame('src/', $module['autoload']['psr-4']['FuteBus\\BookingTracking\\']);
        $this->assertContains('FuteBus\\BookingTracking\\Providers\\BookingTrackingServiceProvider',
            $module['extra']['laravel']['providers']);
    }

    private function controllers(): array
    {
        $paths = [];
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(base_path('packages')));
        foreach ($files as $file) {
            $path = $file->getPathname();
            if ($file->isFile() && preg_match('#[/\\\\]Http[/\\\\]Controllers[/\\\\].*Controller\.php$#', $path)) {
                $paths[] = $path;
            }
        }

        return $paths;
    }
}
