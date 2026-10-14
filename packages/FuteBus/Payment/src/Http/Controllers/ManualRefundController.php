<?php

declare(strict_types=1);

namespace FuteBus\Payment\Http\Controllers;

use FuteBus\Payment\Http\Requests\RefundFilterRequest;
use FuteBus\Payment\Http\Requests\UpdateManualRefundRequest;
use FuteBus\Payment\Services\ManualRefundService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManualRefundController extends Controller
{
    public function index(RefundFilterRequest $request, ManualRefundService $service): View|RedirectResponse|StreamedResponse
    {
        if ($request->routeIs('refunds.index') && $request->user()->isStaff()
            && ! $request->user()->isAdmin() && ! $request->user()->isBusCompany()) {
            return redirect()->route('staff.refunds.index', $request->query());
        }
        $filters = $request->validated();
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

    public function update(UpdateManualRefundRequest $request, ManualRefundService $service, int $refund): JsonResponse
    {
        $input = $request->validated();
        $record = $service->change($request->user(), $refund, $input);

        return response()->json(['status' => $record->status, 'version' => $record->version]);
    }
}
