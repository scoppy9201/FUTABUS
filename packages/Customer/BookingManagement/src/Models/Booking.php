<?php

declare(strict_types=1);

namespace FuteBus\BookingManagement\Models;

use App\Models\User;
use FuteBus\CustomerManagement\Models\Customer;
use FuteBus\Payment\Models\Payment;
use FuteBus\Payment\Models\SePayPaymentIntent;
use FuteBus\Payment\Models\TicketExchange;
use FuteBus\Profile\Models\BookingReview;
use FuteBus\Profile\Models\CustomerFeedbackReport;
use FuteBus\TicketManagement\Models\Ticket;
use FuteBus\TripManagement\Models\Trip;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    protected $table = 'bookings';

    protected $fillable = [
        'booking_code',
        'trip_id',
        'customer_id',
        'user_id',
        'seat_count',
        'total_amount',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'trip_id'      => 'integer',
            'customer_id'  => 'integer',
            'user_id'      => 'integer',
            'seat_count'   => 'integer',
            'total_amount' => 'decimal:2',
            'created_at'   => 'datetime',
            'updated_at'   => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'trip_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function bookedSeats(): HasMany
    {
        return $this->hasMany(BookedSeat::class, 'booking_id', 'id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'booking_id', 'id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'booking_id', 'id');
    }

    public function bookingSepayPaymentIntents(): HasMany
    {
        return $this->hasMany(SePayPaymentIntent::class, 'booking_id', 'id');
    }

    public function secondaryBookingSepayPaymentIntents(): HasMany
    {
        return $this->hasMany(SePayPaymentIntent::class, 'secondary_booking_id', 'id');
    }

    public function bookingReview(): HasOne
    {
        return $this->hasOne(BookingReview::class, 'booking_id', 'id');
    }

    public function customerFeedbackReports(): HasMany
    {
        return $this->hasMany(CustomerFeedbackReport::class, 'booking_id', 'id');
    }

    public function incomingTicketExchange(): HasOne
    {
        return $this->hasOne(TicketExchange::class, 'new_booking_id')->where('status', 'completed');
    }
}
