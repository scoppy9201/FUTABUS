<?php

declare(strict_types=1);

namespace FuteBus\Profile\Models;

use App\Models\User;
use FuteBus\BookingManagement\Models\Booking;
use FuteBus\BusCompanyManagement\Models\BusCompany;
use FuteBus\RolePermission\Models\Role;
use FuteBus\StaffCustomerSupport\Models\CustomerSupportEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerFeedbackReport extends Model
{
    protected $table = 'customer_feedback_reports';

    protected $fillable = [
        'user_id',
        'category',
        'booking_code',
        'subject',
        'message',
        'status',
        'booking_id',
        'urgency',
        'contact_name',
        'contact_phone',
        'contact_email',
        'truth_confirm',
        'allow_contact',
        'response',
        'reviewing_at',
        'resolved_at',
        'assigned_bus_company_id',
        'assigned_staff_id',
        'version',
        'department_role_id',
        'transferred_at',
    ];

    protected function casts(): array
    {
        return [
            'user_id'                 => 'integer',
            'created_at'              => 'datetime',
            'updated_at'              => 'datetime',
            'booking_id'              => 'integer',
            'truth_confirm'           => 'boolean',
            'allow_contact'           => 'boolean',
            'reviewing_at'            => 'datetime',
            'resolved_at'             => 'datetime',
            'assigned_bus_company_id' => 'integer',
            'assigned_staff_id'       => 'integer',
            'version'                 => 'integer',
            'department_role_id'      => 'integer',
            'transferred_at'          => 'datetime',
        ];
    }

    public function assignedBusCompany(): BelongsTo
    {
        return $this->belongsTo(BusCompany::class, 'assigned_bus_company_id', 'id');
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_staff_id', 'id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id');
    }

    public function departmentRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'department_role_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function customerFeedbackAttachments(): HasMany
    {
        return $this->hasMany(CustomerFeedbackAttachment::class, 'report_id', 'id');
    }

    public function customerSupportEvents(): HasMany
    {
        return $this->hasMany(CustomerSupportEvent::class, 'report_id', 'id');
    }
}
