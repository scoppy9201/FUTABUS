<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class GroupApproval extends Model
{
    public $timestamps = false; // chỉ có created_at thủ công

    protected $fillable = [
        'approvable_type', 'approvable_id',
        'user_id', 'quyet_dinh', 'ghi_chu', 'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
