<?php

declare(strict_types=1);

namespace Tests\Feature\Architecture;

use FuteBus\BusCompanyManagement\Models\BusCompany;
use FuteBus\BusManagement\Models\VehicleType;
use FuteBus\Cancellation\Models\TicketCancellation;
use FuteBus\CustomerManagement\Models\Customer;
use FuteBus\Payment\Models\Payment;
use FuteBus\Payment\Models\SePayPaymentIntent;
use FuteBus\Payment\Models\TicketRefund;
use FuteBus\RolePermission\Models\Permission;
use FuteBus\RolePermission\Models\Role;
use FuteBus\TicketManagement\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DomainModelRelationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_route_fleet_schedule_booking_and_refund_share_the_same_foreign_keys(): void
    {
        $user = $this->createTestUser();
        $company = BusCompany::create(['name' => 'FUTA Bus Lines', 'code' => 'FUTA', 'is_active' => true]);
        $type = VehicleType::create(['name' => 'Limousine 34', 'default_capacity' => 34]);
        $bus = $company->buses()->create(['license_plate' => '51H-123.45', 'vehicle_type_id' => $type->id, 'capacity' => 34]);
        $seat = $bus->seatLayouts()->create(['seat_code' => 'A01', 'row_number' => 1, 'column_number' => 1, 'is_available' => true]);
        $route = $company->routes()->create([
            'name' => 'HCM - Da Lat', 'code' => 'HCM-DL', 'origin_city' => 'HCM', 'destination_city' => 'Da Lat', 'base_price' => 350000,
        ]);
        $route->routeStops()->createMany([
            ['name' => 'Departure', 'stop_order' => 1, 'leg_minutes' => 0, 'offset_minutes' => 0],
            ['name' => 'Arrival', 'stop_order' => 2, 'leg_minutes' => 480, 'offset_minutes' => 480],
        ]);
        $schedule = $route->tripSchedules()->create([
            'departure_time' => '08:00', 'days_of_week' => range(1, 7), 'start_date' => today(), 'end_date' => today()->addDays(30), 'price' => 350000,
        ]);
        $trip = $schedule->trips()->create([
            'route_id'       => $route->id, 'bus_id' => $bus->id, 'bus_company_id' => $company->id,
            'departure_time' => today()->addDay()->setTime(8, 0), 'arrival_time' => today()->addDay()->setTime(16, 0), 'price' => 350000,
        ]);
        $returnTrip = $schedule->trips()->create([
            'route_id'       => $route->id, 'bus_id' => $bus->id, 'bus_company_id' => $company->id,
            'departure_time' => today()->addDay()->setTime(18, 0), 'arrival_time' => today()->addDay()->addDay()->setTime(2, 0), 'price' => 350000,
        ]);
        $customer = Customer::create(['user_id' => $user->id, 'full_name' => 'Test customer', 'phone' => '0901234567']);
        $booking = $trip->bookings()->create([
            'booking_code' => 'MODELBK1', 'customer_id' => $customer->id, 'user_id' => $user->id, 'seat_count' => 1, 'total_amount' => 350000,
        ]);
        $booking->bookedSeats()->create(['trip_id' => $trip->id, 'seat_layout_id' => $seat->id, 'seat_code' => 'A01', 'price' => 350000]);
        $ticket = Ticket::create([
            'ticket_code'    => 'MODELTK1', 'booking_id' => $booking->id, 'trip_id' => $trip->id,
            'seat_layout_id' => $seat->id, 'seat_code' => 'A01', 'price' => 350000,
            'passenger_name' => 'Test customer', 'passenger_phone' => '0901234567',
        ]);
        $payment = Payment::create(['payment_code' => 'MODELPAY1', 'booking_id' => $booking->id, 'amount' => 350000,
            'method'                               => 'cash', 'payload' => ['private' => true]]);
        $intent = SePayPaymentIntent::create([
            'token'             => (string) Str::uuid(), 'code' => 'MODELINTENT1', 'trip_id' => $trip->id,
            'secondary_trip_id' => $returnTrip->id, 'booking_id' => $booking->id,
            'seat_ids'          => [$seat->id], 'snapshot' => ['private' => true], 'amount' => 350000, 'expires_at' => now()->addMinutes(15),
        ]);
        $cancellation = TicketCancellation::create([
            'reference'      => (string) Str::uuid(), 'ticket_id' => $ticket->id, 'user_id' => $user->id, 'reason' => 'plan_changed',
            'ticket_amount'  => 350000, 'fee_percent' => 10, 'fee_amount' => 35000, 'refund_amount' => 315000,
            'policy_version' => 'test', 'departure_at' => $trip->departure_time,
        ]);
        $refund = TicketRefund::create([
            'cancellation_id' => $cancellation->id, 'payment_id' => $payment->id, 'idempotency_key' => 'private-key',
            'amount'          => 315000, 'environment' => 'sandbox', 'status' => 'processing', 'assigned_to' => $user->id, 'transaction_id' => 'TEST-ORIGINAL-PAYMENT',
        ]);
        $event = $refund->ticketRefundEvents()->create(['actor_id' => $user->id, 'version' => 1,
            'from_status'                                          => 'pending', 'to_status' => 'processing', 'amount' => 315000]);

        $loaded = $trip->fresh()->load(['route.routeStops', 'tripSchedule', 'bus.vehicleType', 'bus.seatLayouts', 'bookings.customer', 'bookings.payments']);
        $this->assertSame($route->id, $loaded->route->id);
        $this->assertCount(2, $loaded->route->routeStops);
        $this->assertSame($schedule->id, $loaded->tripSchedule->id);
        $this->assertSame(range(1, 7), $loaded->tripSchedule->days_of_week);
        $this->assertSame($type->id, $loaded->bus->vehicleType->id);
        $this->assertSame($seat->id, $loaded->bus->seatLayouts->sole()->id);
        $this->assertSame($customer->id, $loaded->bookings->sole()->customer->id);
        $this->assertSame($payment->id, $loaded->bookings->sole()->payments->sole()->id);
        $this->assertSame($trip->id, $intent->trip->id);
        $this->assertSame($returnTrip->id, $intent->secondaryTrip->id);
        $this->assertSame([$seat->id], $intent->seat_ids);
        $this->assertSame($ticket->id, $refund->cancellation->ticket->id);
        $this->assertSame($cancellation->id, $ticket->ticketCancellation->id);
        $this->assertSame($refund->id, $cancellation->ticketRefund->id);
        $this->assertSame($user->id, $refund->assignedTo->id);
        $this->assertSame($event->id, $refund->ticketRefundEvents->sole()->id);
        $this->assertSame('350000.00', $payment->amount);
        $this->assertSame(315000, $refund->amount);
        $this->assertArrayNotHasKey('token', $intent->toArray());
        $this->assertArrayNotHasKey('snapshot', $intent->toArray());
        $this->assertArrayNotHasKey('payload', $payment->toArray());
        $this->assertArrayNotHasKey('idempotency_key', $refund->toArray());
        $this->assertCount(1, $user->bookings);
    }

    public function test_identity_roles_and_permissions_use_the_existing_pivots(): void
    {
        $user = $this->createTestUser();
        $role = Role::create(['name' => 'Staff', 'slug' => 'staff']);
        $permission = Permission::create(['name' => 'View routes', 'slug' => 'route.view', 'group' => 'route', 'is_active' => true]);
        $role->permissions()->attach($permission);
        $role->users()->attach($user);

        $this->assertTrue($user->hasRole('staff'));
        $this->assertSame($role->id, $user->roles->sole()->id);
        $this->assertSame($user->id, $role->users->sole()->id);
        $this->assertSame($permission->id, $role->permissions->sole()->id);
        $this->assertSame($role->id, $permission->roles->sole()->id);
        $this->assertTrue($permission->is_active);
    }
}
