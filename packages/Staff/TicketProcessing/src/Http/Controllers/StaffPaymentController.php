<?php

declare(strict_types=1);

namespace Staff\TicketProcessing\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Staff\TicketProcessing\Services\CashSaleService;
use Staff\TicketProcessing\Services\CounterSaleService;
use Staff\TicketProcessing\Services\StaffPaymentPresenter;

class StaffPaymentController extends Controller
{
    public function __invoke(Request $request, CounterSaleService $sales, CashSaleService $cash, StaffPaymentPresenter $presenter, string $sale): View
    {
        if (($receipt = $cash->show($request->user(), $sale)) !== null) {
            return view('StaffTickets::sale', ['initialSale' => $presenter->render($receipt)]);
        }
        $draft = $request->session()->get('staff_ticket_payment');
        if (is_array($draft) && $draft['token'] === $sale && $draft['sale']['id'] === null && ! DB::table('sepay_payment_intents')->where('token', $sale)->exists()) {
            abort_unless($draft['operator'] === $request->user()->id && $draft['company'] === $request->user()->bus_company_id, 404);
            abort_if($draft['sale']['preview']['created_at'] + 600 <= now()->timestamp, 410);
            $payment = [...$draft['sale'], 'token' => $sale];
        } else {
            $payment = $sales->show($request->user(), $sale);
        }

        return view('StaffTickets::sale', ['initialSale' => $presenter->render($payment)]);
    }
}
