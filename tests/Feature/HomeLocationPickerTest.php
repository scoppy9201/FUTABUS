<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeLocationPickerTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_location_picker_uses_separate_operator_departure_and_destination_data(): void
    {
        $catalog = json_decode(
            file_get_contents(app_path('Data/futa_booking_locations.json')),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        $this->assertCount(9, $catalog['departure']['provinces']);
        $this->assertCount(13, $catalog['departure']['areas']);
        $this->assertCount(5, $catalog['destination']['provinces']);
        $this->assertCount(5, $catalog['destination']['areas']);
        $this->assertSame('Cà Mau', $catalog['departure']['provinces'][0]);
        $this->assertSame('An Giang', $catalog['destination']['provinces'][0]);
        $this->assertCount(2, $catalog['departure']['areas'][8]['offices']);
        $this->assertSame([], $catalog['destination']['areas'][1]['offices']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('locationCatalog:', false)
            ->assertSee('Tỉnh/Thành phố')
            ->assertSee('Không tìm thấy địa điểm');
    }
}
