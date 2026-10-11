<?php

declare(strict_types=1);

namespace FuteBus\Profile\Http\Controllers;

use FuteBus\Profile\Http\Requests\StoreFeedbackRequest;
use FuteBus\Profile\Services\FeedbackService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FeedbackController extends Controller
{
    public function index(Request $request, FeedbackService $feedback): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(FeedbackService::STATUSES)],
            'search' => ['nullable', 'string', 'max:150'],
        ]);

        return view('Profile::feedback', [
            'user'     => $request->user(),
            'reports'  => $feedback->search($request->user(), $filters),
            'bookings' => $feedback->bookingsForUser($request->user()),
            'filters'  => $filters,
        ]);
    }

    public function store(StoreFeedbackRequest $request, FeedbackService $feedback): RedirectResponse
    {
        $feedback->submit($request->user(), $request->safe()->except('attachments'), $request->file('attachments', []));

        return redirect()->route('profile.feedback.index')
            ->with('status', __('Profile::feedback.sent'));
    }

    public function show(Request $request, int $report, FeedbackService $feedback): Response
    {
        $details = $feedback->findForUser($request->user(), $report);
        abort_if($details === null, 404);

        $view = $request->boolean('modal') ? 'Profile::partials.feedback-detail' : 'Profile::feedback-show';

        return response()->view($view, [
            'user'        => $request->user(),
            'report'      => $details,
            'attachments' => $feedback->attachmentsForReport($report),
            'booking'     => $feedback->bookingForReport($request->user(), $details),
        ])->header('Cache-Control', 'private, no-store');
    }

    public function attachment(Request $request, int $report, int $attachment): StreamedResponse
    {
        $details = DB::table('customer_feedback_reports')->where('id', $report)->first(['user_id']);
        abort_if($details === null, 404);
        abort_unless((int) $details->user_id === (int) $request->user()->id || $request->user()->isAdmin(), 404);

        $image = DB::table('customer_feedback_attachments')->where('report_id', $report)->where('id', $attachment)->first();
        abort_if($image === null, 404);
        $pattern = '#^customer-feedback/'.$report.'/[a-f0-9-]{36}\\.(jpg|png|webp)$#';
        abort_unless(preg_match($pattern, $image->path) === 1 && Storage::disk('local')->exists($image->path), 404);
        abort_unless(in_array($image->mime_type, ['image/jpeg', 'image/png', 'image/webp'], true), 404);

        return Storage::disk('local')->response($image->path, basename($image->path), [
            'Content-Type'           => $image->mime_type,
            'Cache-Control'          => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
