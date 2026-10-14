<?php

declare(strict_types=1);

namespace FuteBus\Profile\Models;

use App\Models\User;
use FuteBus\BookingManagement\Models\Booking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingReview extends Model
{
    protected $table = 'booking_reviews';

    protected $fillable = [
        'booking_id',
        'user_id',
        'rating',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'booking_id' => 'integer',
            'user_id'    => 'integer',
            'rating'     => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
