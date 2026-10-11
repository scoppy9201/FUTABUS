<?php

declare(strict_types=1);

namespace Tests\Feature\TripSearch;

use Database\Seeders\TripSearchDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TripSearchDemoStopsTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_routes_have_addressed_stops_without_duplicate_reseeding(): void
    {
        $this->seed(TripSearchDemoSeeder::class);

        $routeId = DB::table('routes')->where('code', 'DEMO-HCM-LD-01')->value('id');
        $stops = DB::table('route_stops')->where('route_id', $routeId)->orderBy('stop_order')->get();
        $this->assertGreaterThan(2, $stops->count());
        $this->assertSame('Bến xe Miền Tây', $stops->first()->name);
        $this->assertNotEmpty($stops->first()->address);
        $this->assertSame('Đà Lạt', $stops->last()->name);
        $this->assertNotEmpty($stops->last()->address);

        $this->seed(TripSearchDemoSeeder::class);
        $this->assertSame($stops->count(), DB::table('route_stops')->where('route_id', $routeId)->count());
    }
}
