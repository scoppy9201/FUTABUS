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
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManualRefundController extends Controller
{
    public function index(Request $request, ManualRefundService $service): View|RedirectResponse|StreamedResponse
    {
        if ($request->routeIs('refunds.index') && $request->user()->isStaff()
            && ! $request->user()->isAdmin() && ! $request->user()->isBusCompany()) {
            return redirect()->route('staff.refunds.index', $request->query());
        }
        $filters = $request->validate(['status' => ['nullable', Rule::in(['refund_pending', 'refund_processing', 'refunded', 'refund_failed'])],
            'search'                            => ['nullable', 'string', 'max:100'],
            'period'                            => ['nullable', Rule::in(['7', '30', 'all'])],
            'selected'                          => ['nullable', 'integer', 'min:1'],
            'export'                            => ['nullable', Rule::in(['csv'])],
        ]);
        $query = $service->visible($request->user())->where('ticket_refunds.processing_mode', 'manual');
        $summary = (clone $query)->reorder()->select('ticket_refunds.status')
            ->selectRaw('COUNT(*) as total, SUM(ticket_refunds.amount) as amount')
            ->groupBy('ticket_refunds.status')->get()->keyBy('status');
        $query = $service->filter($query, $filters);
        if (($filters['export'] ?? null) === 'csv') {
            return $service->export($query);
        }
        $refunds = $query->orderByDesc('ticket_refunds.id')->paginate(15)->withQueryString();
        $selectedRefund = $refunds->getCollection()->firstWhere('id', (int) ($filters['selected'] ?? 0))
            ?? $refunds->first();

        $staffPortal = $request->routeIs('staff.refunds.*');

        return view($staffPortal ? 'StaffDashboard::refunds' : 'Payment::refunds.index', [
            'refunds'        => $refunds, 'summary' => $summary, 'filters' => $filters, 'section' => 'refunds',
            'selectedRefund' => $selectedRefund,
            'events'         => $selectedRefund ? $service->events($selectedRefund->id) : collect(),
            'refundRoutes'   => $staffPortal ? 'staff.refunds' : 'refunds',
        ]);
    }

    public function show(Request $request, ManualRefundService $service, int $refund): Response
    {
        $record = $service->visible($request->user())->where('ticket_refunds.id', $refund)->first();
        abort_if($record === null, 404);
        $events = $service->events($refund);

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
