<?php

declare(strict_types=1);

namespace FuteBus\Payment\Http\Controllers;

use FuteBus\Payment\Services\ManualRefundService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ManualRefundController extends Controller
{
    public function index(Request $request, ManualRefundService $service): View|RedirectResponse
    {
        if ($request->routeIs('refunds.index') && $request->user()->isStaff()
            && ! $request->user()->isAdmin() && ! $request->user()->isBusCompany()) {
            return redirect()->route('staff.refunds.index', $request->query());
        }
        $filters = $request->validate(['status' => ['nullable', Rule::in(['refund_pending', 'refund_processing', 'refunded', 'refund_failed'])],
            'search'                            => ['nullable', 'string', 'max:100']]);
        $query = $service->visible($request->user())->where('ticket_refunds.processing_mode', 'manual');
        $counts = (clone $query)->reorder()->select('ticket_refunds.status')->selectRaw('COUNT(*) as total')
            ->groupBy('ticket_refunds.status')->pluck('total', 'status');
        if (! empty($filters['status'])) {
            $query->where('ticket_refunds.status', $filters['status']);
        }
        if (! empty($filters['search'])) {
            $query->where(function ($query) use ($filters): void {
                $query->where('ticket_cancellations.reference', 'like', '%'.$filters['search'].'%')
                    ->orWhere('tickets.ticket_code', 'like', '%'.$filters['search'].'%')
                    ->orWhere('bookings.booking_code', 'like', '%'.$filters['search'].'%');
            });
        }

        $staffPortal = $request->routeIs('staff.refunds.*');

        return view($staffPortal ? 'StaffDashboard::refunds' : 'Payment::refunds.index', [
            'refunds'      => $query->orderByDesc('ticket_refunds.id')->paginate(15)->withQueryString(),
            'counts'       => $counts, 'filters' => $filters, 'section' => 'refunds',
            'refundRoutes' => $staffPortal ? 'staff.refunds' : 'refunds',
        ]);
    }

    public function show(Request $request, ManualRefundService $service, int $refund): Response
    {
        $record = $service->visible($request->user())->where('ticket_refunds.id', $refund)->first();
        abort_if($record === null, 404);
        $events = DB::table('ticket_refund_events')->join('users', 'users.id', '=', 'ticket_refund_events.actor_id')
            ->where('refund_id', $refund)->orderBy('version')->select('ticket_refund_events.*', 'users.name')->get();

        return response()->view('Payment::refunds.detail', ['refund' => $record, 'events' => $events,
            'refundRoutes'                                           => $request->routeIs('staff.refunds.*') ? 'staff.refunds' : 'refunds'])
            ->header('Cache-Control', 'private, no-store');
    }

    public function update(Request $request, ManualRefundService $service, int $refund): JsonResponse
    {
        $input = $request->validate([
            'version'        => ['required', 'integer', 'min:0'], 'action' => ['required', Rule::in(['claim', 'complete', 'fail'])],
            'amount'         => ['required_if:action,complete', 'nullable', 'integer', 'min:1'],
            'bank_reference' => ['required_if:action,complete', 'nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9][A-Za-z0-9._\/-]*$/'],
            'bank_paid_at'   => ['required_if:action,complete', 'nullable', 'date', 'before_or_equal:now'],
            'bank_verified'  => ['required_if:action,complete', 'accepted_if:action,complete'],
            'note'           => ['required_if:action,fail', 'nullable', 'string', 'max:1000'],
        ]);
        $record = $service->change($request->user(), $refund, $input);

        return response()->json(['status' => $record->status, 'version' => $record->version]);
    }
}
