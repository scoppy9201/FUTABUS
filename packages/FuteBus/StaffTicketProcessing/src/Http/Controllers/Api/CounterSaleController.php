<?php

declare(strict_types=1);

namespace FuteBus\StaffTicketProcessing\Http\Controllers\Api;

use FuteBus\Core\Http\Controllers\TripBookingController;
use FuteBus\Core\Services\BookingLocationCatalog;
use FuteBus\Core\Services\BookingTripStops;
use FuteBus\Core\Services\TripSearchService;
use FuteBus\Payment\Services\SePayPaymentService;
use FuteBus\StaffDashboard\Services\StaffDashboardService;
use FuteBus\StaffTicketProcessing\Http\Requests\TicketBookingRequest;
use FuteBus\StaffTicketProcessing\Http\Requests\TicketQuoteRequest;
use FuteBus\StaffTicketProcessing\Http\Requests\TicketSaleRequest;
use FuteBus\StaffTicketProcessing\Http\Requests\TicketTripRequest;
use FuteBus\StaffTicketProcessing\Services\CashSaleService;
use FuteBus\StaffTicketProcessing\Services\CounterSaleService;
use FuteBus\StaffTicketProcessing\Services\StaffPaymentPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CounterSaleController extends Controller
{
    public function __construct(private readonly StaffPaymentPresenter $presenter) {}

    public function context(Request $request, StaffDashboardService $dashboard, BookingLocationCatalog $locations, SePayPaymentService $payments): JsonResponse
    {
        $data = $dashboard->data($request->user());

        return response()->json(['data' => [...array_intersect_key($data, array_flip(['employee', 'navigation', 'groups'])), 'locations' => $locations->all(), 'payment_ready' => $payments->ready()]])->header('Cache-Control', 'no-store');
    }

    public function trips(TicketTripRequest $request, CounterSaleService $sales): JsonResponse
    {
        $criteria = $request->validated();
        $trips = $sales->trips($request->user(), $criteria);
        $returns = $criteria['trip_type'] === 'round_trip' && ($criteria['direction'] ?? 'outbound') === 'outbound'
            ? $sales->trips($request->user(), [...$criteria, 'direction' => 'return']) : [];
        $html = view('StaffTicketProcessing::partials.trip-results', [
            'trips'     => $trips, 'returnTrips' => $returns, 'from' => $criteria['departure'], 'to' => $criteria['destination'],
            'roundTrip' => $criteria['trip_type'] === 'round_trip', 'departureDate' => $criteria['departure_date'], 'returnDate' => $criteria['return_date'] ?? null,
        ])->render();

        return response()->json(['data' => $trips, 'return_trips' => $returns, 'html' => $html])->header('Cache-Control', 'no-store');
    }

    public function booking(TicketBookingRequest $request, CounterSaleService $sales, TripBookingController $booking, TripSearchService $search, BookingTripStops $stops, int $trip): JsonResponse
    {
        abort_unless(collect($sales->trips($request->user(), $request->validated()))->contains('id', $trip), 404);
        if ($request->input('trip_type') === 'round_trip') {
            $outbound = [...$request->validated(), 'direction' => 'outbound'];
            abort_unless(collect($sales->trips($request->user(), $outbound))->contains('id', (int) $request->query('outbound_trip')), 404);
        }
        $data = $booking($request, $search, $stops, $trip)->getData();

        return response()->json(['data' => ['html' => view('StaffTicketProcessing::partials.customer-booking', $data)->render()]])->header('Cache-Control', 'no-store');
    }

    public function preview(TicketSaleRequest $request, CounterSaleService $sales): JsonResponse
    {
        return response()->json(['data' => $this->presenter->render($sales->preview($request->user(), $request->validated()))])->header('Cache-Control', 'no-store');
    }

    public function store(TicketSaleRequest $request, CounterSaleService $sales): JsonResponse
    {
        return response()->json(['data' => $this->presenter->render($sales->create($request->user(), $request->validated()))], 201)->header('Cache-Control', 'no-store');
    }

    public function quote(TicketQuoteRequest $request, CounterSaleService $sales): JsonResponse
    {
        return response()->json(['data' => $sales->quote($request->user(), $request->validated())])->header('Cache-Control', 'no-store');
    }

    public function show(Request $request, CounterSaleService $sales, CashSaleService $cash, string $sale): JsonResponse
    {
        return response()->json(['data' => $this->presenter->render($cash->show($request->user(), $sale) ?? $sales->show($request->user(), $sale))])->header('Cache-Control', 'no-store');
    }

    public function cash(Request $request, CounterSaleService $sales, CashSaleService $cash, string $sale): JsonResponse
    {
        $request->validate(['received' => ['required', 'accepted']]);
        if (($receipt = $cash->show($request->user(), $sale)) === null) {
            $draft = $request->hasSession() ? $request->session()->get('staff_ticket_payment') : null;
            abort_unless(is_array($draft) && $draft['token'] === $sale && $draft['operator'] === $request->user()->id && $draft['company'] === $request->user()->bus_company_id, 404);
            $payment = $draft['sale']['id'] === null ? $draft['sale'] : $sales->show($request->user(), $sale);
            $receipt = $cash->collect($request->user(), $sale, $payment['preview']);
        }

        return response()->json(['data' => $this->presenter->render($receipt)])->header('Cache-Control', 'no-store');
    }

    public function destroy(Request $request, CounterSaleService $sales, CashSaleService $cash, string $sale): JsonResponse
    {
        abort_if($cash->show($request->user(), $sale) !== null, 409);
        $draft = $request->hasSession() ? $request->session()->get('staff_ticket_payment') : null;
        if (is_array($draft) && $draft['token'] === $sale && $draft['sale']['id'] === null) {
            abort_unless($draft['operator'] === $request->user()->id && $draft['company'] === $request->user()->bus_company_id, 404);
            $result = [...$draft['sale'], 'status' => 'expired'];
        } else {
            $result = $sales->cancelPreview($request->user(), $sale);
        }
        if (is_array($draft) && $draft['token'] === $sale) {
            $request->session()->forget('staff_ticket_payment');
        }

        return response()->json(['data' => $result])->header('Cache-Control', 'no-store');
    }
}
