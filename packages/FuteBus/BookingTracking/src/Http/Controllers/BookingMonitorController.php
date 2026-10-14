<?php

declare(strict_types=1);

namespace FuteBus\BookingTracking\Http\Controllers;

use FuteBus\BookingTracking\Http\Requests\BookingMonitorRequest;
use FuteBus\BookingTracking\Services\BookingMonitorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class BookingMonitorController extends Controller
{
    public function index(BookingMonitorRequest $request, BookingMonitorService $service): Response|RedirectResponse
    {
        $filters = $request->validated();
        $query = $service->visible($request->user());
        if ($request->routeIs('bookings.index') && $request->user()->isStaff()
            && ! $request->user()->isAdmin() && ! $request->user()->isBusCompany()) {
            return redirect()->route('staff.bookings.index', $request->query())
                ->header('Cache-Control', 'private, no-store');
        }
        $records = $service->filter($query, $filters)
            ->orderByDesc('created_at')->orderBy('kind')->orderByDesc('id')->paginate(15)->withQueryString();
        $records->through(fn (object $record): object => $service->present($record));
        $service->attachPaymentStatuses($records->getCollection());
        $staff = $request->routeIs('staff.bookings.*');

        return response()->view($staff ? 'BookingTracking::bookings.staff' : 'BookingTracking::bookings.index', [
            'records'       => $records, 'filters' => $filters, 'section' => 'bookings',
            'bookingRoutes' => $staff ? 'staff.bookings' : 'bookings',
        ])->header('Cache-Control', 'private, no-store');
    }

    public function show(Request $request, BookingMonitorService $service, string $kind, int $record): Response
    {
        return response()->view('BookingTracking::bookings.detail', $service->detail($request->user(), $kind, $record))
            ->header('Cache-Control', 'private, no-store');
    }
}
