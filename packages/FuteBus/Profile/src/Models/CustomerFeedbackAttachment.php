<?php

declare(strict_types=1);

namespace FuteBus\Profile\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerFeedbackAttachment extends Model
{
    protected $table = 'customer_feedback_attachments';

    protected $fillable = [
        'report_id',
        'path',
        'original_name',
        'mime_type',
        'size_bytes',
    ];

    protected function casts(): array
    {
        return [
            'report_id'  => 'integer',
            'size_bytes' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(CustomerFeedbackReport::class, 'report_id', 'id');
    }
}
