<?php

namespace Tests\Unit\Payment;

use FuteBus\Payment\Services\TicketExchangePolicy;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TicketExchangePolicyTest extends TestCase
{
    public function test_exact_four_hour_boundary_is_eligible_and_one_second_under_is_blocked(): void
    {
        $this->travelTo(now()->startOfSecond());
        $ticket = $this->ticket();
        $ticket->departure_time = now()->addHours(4);
        app(TicketExchangePolicy::class)->eligible($ticket, false);
        $ticket->departure_time = now()->addSeconds(14399);
        $this->expectException(ValidationException::class);
        app(TicketExchangePolicy::class)->eligible($ticket, false);
    }

    #[DataProvider('invalidTickets')]
    public function test_non_eligible_ticket_states_and_repeat_exchanges_are_blocked(array $changes, bool $alreadyExchanged): void
    {
        $ticket = (object) [...(array) $this->ticket(), ...$changes];
        $this->expectException(ValidationException::class);
        app(TicketExchangePolicy::class)->eligible($ticket, $alreadyExchanged);
    }

    public static function invalidTickets(): array
    {
        return [[['status' => 'used'], false], [['status' => 'cancelled'], false], [['booking_status' => 'pending'], false],
            [['trip_status' => 'departed'], false], [['price' => 0], false], [['price' => 100.5], false], [[], true]];
    }

    public function test_equivalence_requires_identical_company_route_class_capacity_and_fare(): void
    {
        $ticket = $this->ticket();
        $target = (object) ['id' => 2, 'status' => 'scheduled', 'route_active' => true, 'bus_status' => 'active',
            'bus_company_id'     => 1, 'route_id' => 1, 'bus_type' => 'limousine', 'capacity' => 34, 'vehicle_type_id' => 3,
            'price'              => 350000, 'departure_time' => now()->addDays(3)];
        $policy = app(TicketExchangePolicy::class);
        $this->assertTrue($policy->equivalent($ticket, $target));
        foreach (['id'       => 1, 'status' => 'cancelled', 'route_active' => false, 'bus_status' => 'maintenance',
            'bus_company_id' => 2, 'route_id' => 2, 'bus_type' => 'sleeper', 'capacity' => 40, 'vehicle_type_id' => 4,
            'price'          => 350001, 'departure_time' => now()->addHours(3)] as $field => $value) {
            $this->assertFalse($policy->equivalent($ticket, (object) [...(array) $target, $field => $value]), $field);
        }
    }

    private function ticket(): object
    {
        return (object) ['id' => 1, 'trip_id' => 1, 'status' => 'active', 'booking_status' => 'confirmed', 'trip_status' => 'scheduled',
            'booking_code'    => 'PAID', 'price' => 350000, 'departure_time' => now()->addDays(2),
            'bus_company_id'  => 1, 'route_id' => 1, 'bus_type' => 'limousine', 'capacity' => 34, 'vehicle_type_id' => 3];
    }
}
