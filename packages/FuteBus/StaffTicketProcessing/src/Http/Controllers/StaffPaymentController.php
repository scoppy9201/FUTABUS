<?php

declare(strict_types=1);

namespace FuteBus\StaffTicketProcessing\Http\Controllers;

use FuteBus\Payment\Services\SePayPaymentService;
use FuteBus\StaffTicketProcessing\Http\Requests\TicketSaleRequest;
use FuteBus\StaffTicketProcessing\Services\CashSaleService;
use FuteBus\StaffTicketProcessing\Services\CounterSaleService;
use FuteBus\StaffTicketProcessing\Services\SaleCancellationService;
use FuteBus\StaffTicketProcessing\Services\StaffPaymentPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StaffPaymentController extends Controller
{
    public function store(TicketSaleRequest $request, CounterSaleService $sales, SePayPaymentService $payments): RedirectResponse
    {
        $data = $request->validated();
        $sale = $payments->ready() ? $sales->create($request->user(), $data) : $sales->preview($request->user(), $data);
        $request->session()->put('staff_ticket_payment', [
            'token'   => $data['idempotency_key'], 'operator' => $request->user()->id,
            'company' => $request->user()->bus_company_id, 'sale' => $sale,
        ]);

        return redirect()->route('staff.tickets.payment', ['sale' => $data['idempotency_key']], 303);
    }

    public function destroy(Request $request, SaleCancellationService $cancellations, string $sale): RedirectResponse
    {
        $draft = $request->session()->get('staff_ticket_payment');
        $result = $cancellations->cancel($request->user(), $sale, is_array($draft) ? $draft : null);
        if (is_array($draft) && $draft['token'] === $sale) {
            $request->session()->forget('staff_ticket_payment');
        }

        return redirect()->route('staff.tickets.sale', $result['preview']['criteria'], 303);
    }

    public function __invoke(Request $request, CounterSaleService $sales, CashSaleService $cash, StaffPaymentPresenter $presenter, string $sale): View
    {
        if (($receipt = $cash->show($request->user(), $sale)) !== null) {
            return view('StaffTicketProcessing::sale', ['initialSale' => $presenter->render($receipt)]);
        }
        $draft = $request->session()->get('staff_ticket_payment');
        if (is_array($draft) && $draft['token'] === $sale && $draft['sale']['id'] === null && ! DB::table('sepay_payment_intents')->where('token', $sale)->exists()) {
            abort_unless($draft['operator'] === $request->user()->id && $draft['company'] === $request->user()->bus_company_id, 404);
            abort_if($draft['sale']['preview']['created_at'] + 600 <= now()->timestamp, 410);
            $payment = [...$draft['sale'], 'token' => $sale];
        } else {
            $payment = $sales->show($request->user(), $sale);
        }

        return view('StaffTicketProcessing::sale', ['initialSale' => $presenter->render($payment)]);
    }
}
