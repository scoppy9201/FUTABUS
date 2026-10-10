<?php

namespace Tests\Feature\Core;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PopularRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_shows_the_three_featured_route_cards_in_reference_order(): void
    {
        app()->setLocale('vi');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Điểm đến được yêu thích')
            ->assertSeeInOrder([
                'images/popular-routes/ho-chi-minh-city.png',
                'images/popular-routes/da-lat.png',
                'images/popular-routes/da-nang.png',
            ], false)
            ->assertSee('310km - 8 giờ')
            ->assertSee('195.000đ')
            ->assertSee('430.000đ');
    }
}
