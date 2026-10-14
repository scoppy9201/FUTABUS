<?php

declare(strict_types=1);

namespace FuteBus\BusManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentTypeField extends Model
{
    protected $table = 'document_type_fields';

    protected $fillable = [
        'document_type_id',
        'field_key',
        'label',
        'field_type',
        'is_required',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'document_type_id' => 'integer',
            'is_required'      => 'boolean',
            'is_active'        => 'boolean',
            'sort_order'       => 'integer',
            'created_at'       => 'datetime',
            'updated_at'       => 'datetime',
        ];
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id', 'id');
    }
}
