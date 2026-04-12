<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CurrencyHistory extends Model
{
    protected $fillable = [
        'user_id',
        'from_currency',
        'to_currency',
        'amount',
        'result',
        'rate',
    ];

    protected $casts = [
        'amount' => 'float',
        'result' => 'float',
        'rate'   => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
