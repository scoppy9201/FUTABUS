<?php

declare(strict_types=1);

namespace FuteBus\StaffCustomerSupport\Models;

use App\Models\User;
use FuteBus\BusCompanyManagement\Models\BusCompany;
use FuteBus\Profile\Models\CustomerFeedbackReport;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerSupportEvent extends Model
{
    protected $table = 'customer_support_events';

    public const UPDATED_AT = null;

    protected $fillable = [
        'report_id',
        'actor_id',
        'actor_name',
        'kind',
        'previous_status',
        'status',
        'response',
        'previous_bus_company_id',
        'bus_company_id',
        'version',
        'idempotency_key',
        'request_hash',
        'internal_note',
        'department_name',
    ];

    protected $hidden = ['idempotency_key', 'request_hash'];

    protected function casts(): array
    {
        return [
            'report_id'               => 'integer',
            'actor_id'                => 'integer',
            'previous_bus_company_id' => 'integer',
            'bus_company_id'          => 'integer',
            'version'                 => 'integer',
            'created_at'              => 'datetime',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id', 'id');
    }

    public function busCompany(): BelongsTo
    {
        return $this->belongsTo(BusCompany::class, 'bus_company_id', 'id');
    }

    public function previousBusCompany(): BelongsTo
    {
        return $this->belongsTo(BusCompany::class, 'previous_bus_company_id', 'id');
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(CustomerFeedbackReport::class, 'report_id', 'id');
    }
}
