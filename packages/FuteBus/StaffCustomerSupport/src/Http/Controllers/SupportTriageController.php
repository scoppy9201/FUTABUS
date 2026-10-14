<?php

declare(strict_types=1);

namespace FuteBus\StaffCustomerSupport\Http\Controllers;

use FuteBus\StaffCustomerSupport\Services\FeedbackProcessingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class SupportTriageController extends Controller
{
    public function index(Request $request, FeedbackProcessingService $support): View
    {
        abort_unless($request->user()->isAdmin(), 403);

        return view('StaffCustomerSupport::support-triage', [
            'reports'   => $support->visible($request->user())->orderByDesc('feedback.id')->paginate(15),
            'companies' => DB::table('bus_companies')->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function assign(Request $request, FeedbackProcessingService $support, int $report): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        $input = $request->validate(['bus_company_id' => ['required', 'integer', 'min:1'], 'version' => ['required', 'integer', 'min:0']]);
        $support->assign($request->user(), $report, (int) $input['bus_company_id'], (int) $input['version']);

        return redirect()->route('staff.support-triage')->with('status', __('StaffCustomerSupport::support.assigned'));
    }
}
