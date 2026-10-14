<?php

declare(strict_types=1);

namespace FuteBus\BusManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleDocument extends Model
{
    protected $table = 'vehicle_documents';

    protected $fillable = [
        'bus_id',
        'document_type_id',
        'renewed_from_id',
        'issued_date',
        'expiry_date',
        'field_values',
        'file_path',
        'file_name',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'bus_id'           => 'integer',
            'document_type_id' => 'integer',
            'renewed_from_id'  => 'integer',
            'issued_date'      => 'date',
            'expiry_date'      => 'date',
            'field_values'     => 'array',
            'created_at'       => 'datetime',
            'updated_at'       => 'datetime',
        ];
    }

    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class, 'bus_id', 'id');
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id', 'id');
    }

    public function renewedFrom(): BelongsTo
    {
        return $this->belongsTo(VehicleDocument::class, 'renewed_from_id', 'id');
    }

    public function vehicleDocuments(): HasMany
    {
        return $this->hasMany(VehicleDocument::class, 'renewed_from_id', 'id');
    }
}
