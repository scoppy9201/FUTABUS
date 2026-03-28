<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletAdjustment extends Model
{
    protected $table = 'wallet_adjustments';

    protected $fillable = [
        'user_id',
        'wallet_id',
        'so_du_truoc',
        'so_du_sau',
        'chenh_lech',
        'transaction_id',
        'ly_do',
    ];

    protected $casts = [
        'so_du_truoc' => 'decimal:2',
        'so_du_sau'   => 'decimal:2',
        'chenh_lech'  => 'decimal:2',
    ];

    public function wallet()
    {
        return $this->belongsTo(MoneyWallet::class, 'wallet_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
