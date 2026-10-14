<?php

declare(strict_types=1);

namespace FuteBus\BusManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentType extends Model
{
    protected $table = 'document_types';

    protected $fillable = [
        'name',
        'description',
        'is_required',
        'has_expiry',
        'blocks_dispatch',
        'warning_days',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_required'     => 'boolean',
            'has_expiry'      => 'boolean',
            'blocks_dispatch' => 'boolean',
            'warning_days'    => 'integer',
            'created_at'      => 'datetime',
            'updated_at'      => 'datetime',
        ];
    }

    public function documentTypeFields(): HasMany
    {
        return $this->hasMany(DocumentTypeField::class, 'document_type_id', 'id');
    }

    public function vehicleDocuments(): HasMany
    {
        return $this->hasMany(VehicleDocument::class, 'document_type_id', 'id');
    }
}
