<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransfer extends Model
{
    protected $table = 'wallet_transfers';

    protected $fillable = [
        'user_id',
        'from_wallet_id',
        'to_wallet_id',
        'so_tien',
        'phi_chuyen',
        'from_transaction_id',
        'to_transaction_id',
        'category_id',
        'ngay_chuyen',
        'ghi_chu',
    ];

    protected $casts = [
        'so_tien'    => 'decimal:2',
        'phi_chuyen' => 'decimal:2',
        'ngay_chuyen'=> 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fromWallet()
    {
        return $this->belongsTo(MoneyWallet::class, 'from_wallet_id');
    }

    public function toWallet()
    {
        return $this->belongsTo(MoneyWallet::class, 'to_wallet_id');
    }

    public function fromTransaction()
    {
        return $this->belongsTo(Transaction::class, 'from_transaction_id');
    }

    public function toTransaction()
    {
        return $this->belongsTo(Transaction::class, 'to_transaction_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
