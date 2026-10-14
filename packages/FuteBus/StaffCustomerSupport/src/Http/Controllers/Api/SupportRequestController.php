<?php

declare(strict_types=1);

namespace FuteBus\StaffCustomerSupport\Http\Controllers\Api;

use FuteBus\StaffCustomerSupport\Services\FeedbackProcessingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupportRequestController extends Controller
{
    public function index(Request $request, FeedbackProcessingService $support): JsonResponse
    {
        $filters = $request->validate([
            'search'   => ['nullable', 'string', 'max:150'], 'status' => ['nullable', Rule::in(['received', 'reviewing', 'waiting', 'resolved'])],
            'category' => ['nullable', Rule::in(['staff', 'trip_service', 'schedule', 'booking_payment', 'lost_property', 'pickup', 'other'])],
            'queue'    => ['nullable', Rule::in(['all', 'unassigned', 'mine', 'waiting', 'resolved'])],
            'urgency'  => ['nullable', Rule::in(['suggestion', 'complaint', 'urgent'])], 'page' => ['sometimes', 'integer', 'min:1'],
        ]);
        $query = $support->visible($request->user());
        match ($filters['queue'] ?? 'all') {
            'unassigned' => $query->where('feedback.status', 'received')->whereNull('feedback.assigned_staff_id'),
            'mine'       => $query->where('feedback.assigned_staff_id', $request->user()->id),
            'waiting', 'resolved' => $query->where('feedback.status', $filters['queue']),
            default => null,
        };
        foreach (['status', 'category', 'urgency'] as $filter) {
            if (filled($filters[$filter] ?? null)) {
                $query->where('feedback.'.$filter, $filters[$filter]);
            }
        }
        if (filled($filters['search'] ?? null)) {
            $search = trim($filters['search']);
            $query->where(function ($query) use ($search): void {
                $query->where('feedback.subject', 'like', '%'.$search.'%')->orWhere('feedback.booking_code', 'like', '%'.$search.'%');
                if (preg_match('/^PA-0*([1-9][0-9]*)$/i', $search, $match)) {
                    $query->orWhere('feedback.id', $match[1]);
                }
            });
        }
        $page = $query->orderByDesc('feedback.created_at')->orderByDesc('feedback.id')->paginate(15);

        return response()->json(['data' => $page->getCollection()->map($this->resource(...)), 'meta' => [
            'current_page' => $page->currentPage(), 'last_page' => $page->lastPage(), 'total' => $page->total(),
            'from'         => $page->firstItem(), 'to' => $page->lastItem(),
        ]])->header('Cache-Control', 'private, no-store');
    }

    public function show(Request $request, FeedbackProcessingService $support, int $report): JsonResponse
    {
        $item = $support->visible($request->user())->where('feedback.id', $report)->first();
        abort_if($item === null, 404);
        $history = $support->history($report, true)->map(function (object $event): array {
            return [...(array) $event, 'created_at' => Carbon::parse($event->created_at)->toIso8601String()];
        });
        $images = DB::table('customer_feedback_attachments')->where('report_id', $report)->orderBy('id')->get(['id', 'original_name']);

        return response()->json(['data' => [...$this->resource($item),
            'departments'  => $support->departments($request->user()),
            'can_claim'    => $item->department_role_id === null || DB::table('role_user')->where('user_id', $request->user()->id)->where('role_id', $item->department_role_id)->exists(),
            'updated_at'   => Carbon::parse($item->updated_at)->toIso8601String(),
            'departure_at' => $item->departure_time ? Carbon::parse($item->departure_time)->toIso8601String() : null,
            'seat_codes'   => $item->booking_id ? DB::table('booked_seats')->where('booking_id', $item->booking_id)->orderBy('seat_code')->pluck('seat_code') : [],
            'message'      => $item->message, 'response' => $item->response, 'history' => $history,
            'contact'      => ['name' => $item->contact_name, 'allow_contact' => (bool) $item->allow_contact,
                'phone'               => $item->allow_contact ? $item->contact_phone : null,
                'email'               => $item->allow_contact ? $item->contact_email : null],
            'can_respond' => $request->user()->hasPermissionTo('support.respond'),
            'is_handler'  => (int) $item->assigned_staff_id === $request->user()->id,
            'attachments' => $images->map(fn (object $image): array => ['id' => $image->id, 'name' => $image->original_name,
                'url'                                                        => route('api.v1.staff.support.attachments', ['report' => $report, 'attachment' => $image->id])]),
        ]])->header('Cache-Control', 'private, no-store');
    }

    public function claim(Request $request, FeedbackProcessingService $support, int $report): JsonResponse
    {
        $input = $request->validate(['version' => ['required', 'integer', 'min:0']]);
        $item = $support->claim($request->user(), $report, (int) $input['version']);

        return response()->json(['data' => ['id' => $item->id, 'version' => $item->version, 'status' => $item->status]])
            ->header('Cache-Control', 'private, no-store');
    }

    public function respond(Request $request, FeedbackProcessingService $support, int $report): JsonResponse
    {
        if (is_string($request->input('response'))) {
            $request->merge(['response' => trim($request->input('response'))]);
        }
        $input = $request->validate([
            'version'  => ['required', 'integer', 'min:0'], 'status' => ['required', Rule::in(['reviewing', 'resolved'])],
            'response' => ['required', 'string', 'max:3000'], 'idempotency_key' => ['required', 'uuid'],
        ]);
        $event = $support->respond($request->user(), $report, $input);

        return response()->json(['data' => ['id' => $event->id, 'report_id' => $report, 'version' => $event->version,
            'status'                             => $event->status, 'response' => $event->response]], 201)
            ->header('Location', route('api.v1.staff.support.responses.show', ['report' => $report, 'response' => $event->id]))
            ->header('Cache-Control', 'private, no-store');
    }

    public function response(Request $request, FeedbackProcessingService $support, int $report, int $response): JsonResponse
    {
        abort_unless($support->visible($request->user())->where('feedback.id', $report)->exists(), 404);
        $event = DB::table('customer_support_events')->where('report_id', $report)->where('id', $response)->where('kind', 'response')
            ->first(['id', 'report_id', 'actor_name', 'status', 'response', 'version', 'created_at']);
        abort_if($event === null, 404);
        $event->created_at = Carbon::parse($event->created_at)->toIso8601String();

        return response()->json(['data' => $event])->header('Cache-Control', 'private, no-store');
    }

    public function transfer(Request $request, FeedbackProcessingService $support, int $report): JsonResponse
    {
        return $this->action($request, $support, $report, 'transfer');
    }

    public function note(Request $request, FeedbackProcessingService $support, int $report): JsonResponse
    {
        return $this->action($request, $support, $report, 'note');
    }

    public function event(Request $request, FeedbackProcessingService $support, int $report, int $event): JsonResponse
    {
        abort_unless($support->visible($request->user())->where('feedback.id', $report)->exists(), 404);
        $item = $support->history($report, true)->firstWhere('id', $event);
        abort_if($item === null || ! in_array($item->kind, ['transfer', 'note'], true), 404);

        return response()->json(['data' => $item])->header('Cache-Control', 'private, no-store');
    }

    private function action(Request $request, FeedbackProcessingService $support, int $report, string $kind): JsonResponse
    {
        if (is_string($request->input('content'))) {
            $request->merge(['content' => trim($request->input('content'))]);
        }
        $rules = ['version' => ['required', 'integer', 'min:0'], 'content' => ['required', 'string', 'max:3000'], 'idempotency_key' => ['required', 'uuid']];
        if ($kind === 'transfer') {
            $rules['department_role_id'] = ['required', 'integer', 'min:1'];
        }
        $event = $support->action($request->user(), $report, $kind, $request->validate($rules));

        return response()->json(['data' => $event], 201)
            ->header('Location', route('api.v1.staff.support.events.show', ['report' => $report, 'event' => $event->id]))
            ->header('Cache-Control', 'private, no-store');
    }

    public function attachment(Request $request, FeedbackProcessingService $support, int $report, int $attachment): StreamedResponse
    {
        abort_unless($support->visible($request->user())->where('feedback.id', $report)->exists(), 404);
        $image = DB::table('customer_feedback_attachments')->where('report_id', $report)->where('id', $attachment)->first();
        abort_if($image === null, 404);
        abort_unless(preg_match('#^customer-feedback/'.$report.'/[a-f0-9-]{36}\.(jpg|png|webp)$#', $image->path) === 1
            && in_array($image->mime_type, ['image/jpeg', 'image/png', 'image/webp'], true)
            && Storage::disk('local')->exists($image->path), 404);

        return Storage::disk('local')->response($image->path, basename($image->path), [
            'Content-Type' => $image->mime_type, 'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function resource(object $item): array
    {
        return ['id'       => (int) $item->id, 'reference' => 'PA-'.str_pad((string) $item->id, 6, '0', STR_PAD_LEFT),
            'subject'      => $item->subject, 'category' => $item->category, 'urgency' => $item->urgency, 'status' => $item->status,
            'version'      => (int) $item->version, 'handler' => $item->handler_name, 'assigned_staff_id' => $item->assigned_staff_id,
            'department'   => $item->department_name, 'department_role_id' => $item->department_role_id,
            'booking_code' => $item->booking_code, 'origin' => $item->origin_city, 'destination' => $item->destination_city,
            'created_at'   => Carbon::parse($item->created_at)->toIso8601String()];
    }
}
