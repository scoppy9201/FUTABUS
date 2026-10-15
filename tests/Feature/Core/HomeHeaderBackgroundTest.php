<?php

namespace Tests\Feature\Core;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeHeaderBackgroundTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_and_search_reload_keep_the_same_background_before_and_after_logout(): void
    {
        $image = asset('images/banners/Abstract Orange Gradient Waves.png');
        $url = route('trip-search', [
            'departure'      => 'TP. Hồ Chí Minh', 'destination' => 'Đà Lạt',
            'departure_date' => now()->addDay()->toDateString(), 'trip_type' => 'one_way', 'quantity' => 1,
        ]);
        $this->get($url)->assertOk()->assertSee('background-image: url(\''.$image.'\')');
        $this->actingAs(User::factory()->create());
        $this->get($url)->assertOk()->assertSee('background-image: url(\''.$image.'\')');
        $this->get($url)->assertOk()->assertSee('background-image: url(\''.$image.'\')');
        $this->post(route('logout'))->assertRedirect(route('home'));
        $this->get($url)->assertOk()->assertSee('background-image: url(\''.$image.'\')')
            ->assertSee('rel="preload" as="image" href="'.$image.'"', false);
    }

    public function test_home_reload_and_return_use_the_application_asset_url_for_the_header(): void
    {
        $image = asset('images/banners/Abstract Orange Gradient Waves.png');
        $this->assertFileExists(public_path('images/banners/Abstract Orange Gradient Waves.png'));
        $this->get(route('home'))->assertOk()
            ->assertSee('background-image: url(\''.$image.'\')')
            ->assertSee('rel="preload" as="image" href="'.$image.'"', false);
        $this->actingAs(User::factory()->create())->withHeader('Referer', route('home').'#trip-search')
            ->get(route('home'))->assertOk()
            ->assertSee('background-image: url(\''.$image.'\')');
        $this->post(route('logout'))->assertRedirect(route('home'));
        $this->assertGuest();
        $this->get(route('home'))->assertOk()
            ->assertSee('background-image: url(\''.$image.'\')');
    }
}
