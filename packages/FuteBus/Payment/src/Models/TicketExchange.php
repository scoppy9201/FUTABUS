<?php

declare(strict_types=1);

namespace FuteBus\Payment\Models;

use App\Models\User;
use FuteBus\BookingManagement\Models\Booking;
use FuteBus\SeatManagement\Models\SeatLayout;
use FuteBus\TicketManagement\Models\Ticket;
use FuteBus\TripManagement\Models\Trip;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketExchange extends Model
{
    protected $fillable = [
        'token', 'user_id', 'old_ticket_id', 'new_ticket_id', 'new_booking_id', 'payment_id',
        'target_trip_id', 'seat_layout_id', 'actor_id', 'status', 'fare', 'fee', 'policy_version',
        'original_departure_at', 'expires_at', 'completed_at', 'snapshot',
    ];

    protected function casts(): array
    {
        return ['snapshot'   => 'array', 'fare' => 'integer', 'fee' => 'integer', 'payment_id' => 'integer', 'actor_id' => 'integer',
            'user_id'        => 'integer', 'old_ticket_id' => 'integer', 'new_ticket_id' => 'integer', 'new_booking_id' => 'integer',
            'target_trip_id' => 'integer', 'seat_layout_id' => 'integer',
            'expires_at'     => 'datetime', 'completed_at' => 'datetime', 'original_departure_at' => 'datetime'];
    }

    public function oldTicket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'old_ticket_id');
    }

    public function newTicket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'new_ticket_id');
    }

    public function newBooking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'new_booking_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function targetTrip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'target_trip_id');
    }

    public function seatLayout(): BelongsTo
    {
        return $this->belongsTo(SeatLayout::class);
    }
}
