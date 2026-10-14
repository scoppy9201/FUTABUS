<?php

declare(strict_types=1);

namespace FuteBus\CustomerManagement\Models;

use App\Models\User;
use FuteBus\BookingManagement\Models\Booking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $table = 'customers';

    protected $fillable = [
        'user_id',
        'full_name',
        'email',
        'phone',
        'identity_number',
        'date_of_birth',
        'gender',
        'address',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'user_id'       => 'integer',
            'date_of_birth' => 'date',
            'created_at'    => 'datetime',
            'updated_at'    => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'customer_id', 'id');
    }
}
