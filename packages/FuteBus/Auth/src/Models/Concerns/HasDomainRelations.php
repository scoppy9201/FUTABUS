<?php

declare(strict_types=1);

namespace FuteBus\Auth\Models\Concerns;

use FuteBus\BookingManagement\Models\Booking;
use FuteBus\BusCompanyManagement\Models\BusCompany;
use FuteBus\Cancellation\Models\TicketCancellation;
use FuteBus\CustomerManagement\Models\Customer;
use FuteBus\Payment\Models\TicketRefund;
use FuteBus\Payment\Models\TicketRefundEvent;
use FuteBus\Profile\Models\BookingReview;
use FuteBus\Profile\Models\CustomerFeedbackReport;
use FuteBus\RolePermission\Models\Role;
use FuteBus\StaffCustomerSupport\Models\CustomerSupportEvent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasDomainRelations
{
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'user_id', 'id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'user_id', 'id');
    }

    public function ticketCancellations(): HasMany
    {
        return $this->hasMany(TicketCancellation::class, 'user_id', 'id');
    }

    public function ticketRefunds(): HasMany
    {
        return $this->hasMany(TicketRefund::class, 'assigned_to', 'id');
    }

    public function ticketRefundEvents(): HasMany
    {
        return $this->hasMany(TicketRefundEvent::class, 'actor_id', 'id');
    }

    public function bookingReviews(): HasMany
    {
        return $this->hasMany(BookingReview::class, 'user_id', 'id');
    }

    public function assignedStaffCustomerFeedbackReports(): HasMany
    {
        return $this->hasMany(CustomerFeedbackReport::class, 'assigned_staff_id', 'id');
    }

    public function userCustomerFeedbackReports(): HasMany
    {
        return $this->hasMany(CustomerFeedbackReport::class, 'user_id', 'id');
    }

    public function customerSupportEvents(): HasMany
    {
        return $this->hasMany(CustomerSupportEvent::class, 'actor_id', 'id');
    }

    public function busCompany(): BelongsTo
    {
        return $this->belongsTo(BusCompany::class, 'bus_company_id', 'id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
    }
}
