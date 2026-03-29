<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrTransfer extends Model
{
    protected $fillable = [
        'sender_id', 'receiver_id', 'sender_wallet_id', 'receiver_wallet_id',
        'so_tien', 'ghi_chu', 'qr_token', 'trang_thai', 'expires_at', 'completed_at',
    ];

    protected $casts = [
        'expires_at'   => 'datetime',
        'completed_at' => 'datetime',
        'so_tien'      => 'decimal:2',
    ];

    public function sender()       { return $this->belongsTo(User::class, 'sender_id'); }
    public function receiver()     { return $this->belongsTo(User::class, 'receiver_id'); }
    public function senderWallet() { return $this->belongsTo(MoneyWallet::class, 'sender_wallet_id'); }
    public function receiverWallet(){ return $this->belongsTo(MoneyWallet::class, 'receiver_wallet_id'); }

    public function isPending()  { return $this->trang_thai === 'pending'; }
    public function isExpired()  { return $this->expires_at->isPast(); }
    public function isUsable()   { return $this->isPending() && !$this->isExpired(); }
}
