<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; 

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens; 

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'ngay_sinh',
        'gioi_tinh',
        'google_id',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'ngay_sinh' => 'date',
    ];

    public function moneyWallets()
    {
        return $this->hasMany(\App\Models\MoneyWallet::class);
    }
}
