<?php

declare(strict_types=1);

namespace FuteBus\StaffDashboard\Services;

use App\Models\User;
use FuteBus\Payment\Services\ManualRefundService;
use FuteBus\StaffCustomerSupport\Services\FeedbackProcessingService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StaffActivityService
{
    public function data(User $staff, string $month): array
    {
        abort_unless($staff->isStaff() && $staff->is_active && $staff->email_verified_at, 403);
        $start = Carbon::createFromFormat('!Y-m', $month, config('app.timezone'))->startOfMonth();
        $end = $start->copy()->addMonth();
        $company = $staff->bus_company_id;
        $access = [
            'issued'          => $company !== null && $staff->hasPermissionTo(['ticket.view', 'booking.create']),
            'cancelled'       => $company !== null && $staff->hasPermissionTo('ticket.view'),
            'support_pending' => $company !== null && $staff->hasPermissionTo('support.view'),
            'refund_pending'  => $company !== null && $staff->hasPermissionTo('payment.view'),
        ];
        $counts = [];
        $queue = collect();
        if ($access['issued']) {
            $issued = $this->tickets($company)->whereIn('bookings.status', ['confirmed', 'completed', 'cancelled'])
                ->whereNotExists(fn (Builder $query) => $query->selectRaw('1')->from('ticket_exchanges')
                    ->whereColumn('ticket_exchanges.new_ticket_id', 'tickets.id')->where('ticket_exchanges.status', 'completed'));
            $counts['issued'] = $this->daily($issued, 'tickets.created_at', $start, $end);
        }
        if ($access['cancelled']) {
            $cancellations = $this->tickets($company)->join('ticket_cancellations', 'ticket_cancellations.ticket_id', '=', 'tickets.id');
            $counts['cancelled'] = $this->daily($cancellations, 'ticket_cancellations.created_at', $start, $end);
        }
        if ($access['support_pending']) {
            $support = app(FeedbackProcessingService::class)->visible($staff)->whereIn('feedback.status', ['waiting', 'received', 'reviewing']);
            $counts['support_pending'] = $this->daily(clone $support, 'feedback.created_at', $start, $end);
            $support->where(fn (Builder $query) => $query->whereNull('feedback.assigned_staff_id')->orWhere('feedback.assigned_staff_id', $staff->id));
            foreach ($support->orderBy('feedback.created_at')->orderBy('feedback.id')->limit(3)
                ->get(['feedback.id', 'feedback.subject', 'feedback.created_at']) as $item) {
                $queue->push(['id' => 'support-'.$item->id, 'kind' => 'support', 'reference' => 'PA-'.str_pad((string) $item->id, 6, '0', STR_PAD_LEFT),
                    'title'        => $item->subject, 'created_at' => Carbon::parse($item->created_at)->toIso8601String(),
                    'url'          => route('staff.support', ['report' => $item->id])]);
            }
        }
        if ($access['refund_pending']) {
            $refunds = app(ManualRefundService::class)->visible($staff)
                ->join('buses', 'buses.id', '=', 'trips.bus_id')->where('buses.bus_company_id', $company)
                ->where('routes.bus_company_id', $company)->whereColumn('bookings.trip_id', 'tickets.trip_id')
                ->whereIn('ticket_refunds.status', ['refund_pending', 'refund_processing', 'refund_failed']);
            $counts['refund_pending'] = $this->daily(clone $refunds, 'ticket_refunds.created_at', $start, $end);
            $refunds->where(fn (Builder $query) => $query->whereNull('ticket_refunds.assigned_to')->orWhere('ticket_refunds.assigned_to', $staff->id));
            foreach ($refunds->orderBy('ticket_refunds.created_at')->orderBy('ticket_refunds.id')->limit(3)->get() as $item) {
                $queue->push(['id' => 'refund-'.$item->id, 'kind' => 'refund', 'reference' => $item->reference,
                    'title'        => $item->ticket_code, 'created_at' => Carbon::parse($item->created_at)->toIso8601String(),
                    'url'          => route('staff.refunds.index', ['selected' => $item->id])]);
            }
        }
        $days = [];
        for ($date = $start->copy(); $date->lt($end); $date->addDay()) {
            $key = $date->toDateString();
            $day = ['date' => $key];
            foreach ($access as $kind => $allowed) {
                $day[$kind] = $allowed ? (int) ($counts[$kind][$key] ?? 0) : null;
            }
            $days[] = $day;
        }

        return ['month' => $month, 'days' => $days, 'access' => $access,
            'queue'     => $queue->sortBy('created_at')->take(3)->values()->all(), 'updated_at' => now()->toIso8601String()];
    }

    private function tickets(int $company): Builder
    {
        return DB::table('tickets')->join('bookings', 'bookings.id', '=', 'tickets.booking_id')
            ->join('trips', 'trips.id', '=', 'tickets.trip_id')->join('routes', 'routes.id', '=', 'trips.route_id')
            ->join('buses', 'buses.id', '=', 'trips.bus_id')->whereColumn('bookings.trip_id', 'tickets.trip_id')
            ->where('trips.bus_company_id', $company)->where('routes.bus_company_id', $company)->where('buses.bus_company_id', $company);
    }

    private function daily(Builder $query, string $column, Carbon $start, Carbon $end): array
    {
        return $query->where($column, '>=', $start)->where($column, '<', $end)
            ->select([])->selectRaw("DATE({$column}) as activity_day, COUNT(*) as total")->groupByRaw("DATE({$column})")
            ->pluck('total', 'activity_day')->all();
    }
}
