<?php

declare(strict_types=1);

namespace FuteBus\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SePayTransaction extends Model
{
    protected $table = 'sepay_transactions';

    protected $fillable = [
        'sepay_id',
        'payment_intent_id',
        'code',
        'account_number',
        'amount',
        'status',
        'payload',
        'environment',
    ];

    protected $hidden = ['account_number', 'payload'];

    protected function casts(): array
    {
        return [
            'sepay_id'          => 'integer',
            'payment_intent_id' => 'integer',
            'amount'            => 'decimal:2',
            'payload'           => 'array',
            'created_at'        => 'datetime',
            'updated_at'        => 'datetime',
        ];
    }

    public function paymentIntent(): BelongsTo
    {
        return $this->belongsTo(SePayPaymentIntent::class, 'payment_intent_id', 'id');
    }
}
