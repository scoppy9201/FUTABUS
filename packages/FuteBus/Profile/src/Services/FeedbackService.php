<?php

declare(strict_types=1);

namespace FuteBus\Profile\Services;

use App\Models\User;
use FuteBus\BookingManagement\Models\Booking as BookingRecord;
use FuteBus\Profile\Models\CustomerFeedbackAttachment as CustomerFeedbackAttachmentRecord;
use FuteBus\Profile\Models\CustomerFeedbackReport as CustomerFeedbackReportRecord;
use FuteBus\StaffCustomerSupport\Services\FeedbackProcessingService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use stdClass;
use Throwable;

class FeedbackService
{
    public const STATUSES = ['received', 'reviewing', 'resolved'];

    public function search(User $user, array $filters): LengthAwarePaginator
    {
        $query = CustomerFeedbackReportRecord::query()->toBase()->where('user_id', $user->id);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $keyword = '%'.$filters['search'].'%';
            $query->where(function (Builder $query) use ($keyword, $filters): void {
                $query->where('subject', 'like', $keyword)
                    ->orWhere('booking_code', 'like', $keyword);

                if (preg_match('/^PA-0*([1-9][0-9]*)$/i', $filters['search'], $matches)) {
                    $query->orWhere('id', $matches[1]);
                }
            });
        }

        return $query->orderByDesc('created_at')->orderByDesc('id')->paginate(10)->withQueryString();
    }

    public function bookingsForUser(User $user): Collection
    {
        return $this->ownedBookings($user)->orderByDesc('trips.departure_time')->get();
    }

    public function findForUser(User $user, int $reportId): ?stdClass
    {
        return CustomerFeedbackReportRecord::query()->toBase()->where('user_id', $user->id)->where('id', $reportId)->first();
    }

    public function bookingForReport(User $user, object $report): ?stdClass
    {
        return $report->booking_id === null ? null : $this->ownedBookings($user)->where('bookings.id', $report->booking_id)->first();
    }

    public function attachmentsForReport(int $reportId): Collection
    {
        return CustomerFeedbackAttachmentRecord::query()->toBase()->where('report_id', $reportId)
            ->orderBy('id')->get(['id', 'report_id', 'original_name', 'mime_type', 'size_bytes', 'created_at']);
    }

    /** @param array<int, UploadedFile> $attachments */
    public function submit(User $user, array $details, array $attachments = []): int
    {
        $storedPaths = [];

        try {
            return DB::transaction(function () use ($user, $details, $attachments, &$storedPaths): int {
                $booking = null;
                if (! empty($details['booking_id'])) {
                    $booking = $this->ownedBookings($user)->where('bookings.id', $details['booking_id'])->first();
                    if ($booking === null) {
                        throw ValidationException::withMessages(['booking_id' => __('Profile::feedback.validation.booking_owned')]);
                    }
                }

                $timestamp = now();
                $reportId = CustomerFeedbackReportRecord::query()->toBase()->insertGetId([
                    'user_id'                 => $user->id,
                    'booking_id'              => $booking?->id,
                    'assigned_bus_company_id' => $booking !== null
                        && $booking->trip_company === $booking->route_company && $booking->trip_company === $booking->bus_company
                        ? $booking->trip_company : null,
                    'booking_code'  => $booking?->booking_code,
                    'category'      => $details['category'],
                    'urgency'       => $details['urgency'],
                    'subject'       => $details['subject'],
                    'message'       => $details['message'],
                    'contact_name'  => $details['contact_name'],
                    'contact_phone' => $details['contact_phone'],
                    'contact_email' => $details['contact_email'] ?? null,
                    'truth_confirm' => true,
                    'allow_contact' => (bool) ($details['allow_contact'] ?? false),
                    'status'        => 'received',
                    'created_at'    => $timestamp,
                    'updated_at'    => $timestamp,
                ]);

                foreach ($attachments as $attachment) {
                    $this->storeAttachment($reportId, $attachment, $storedPaths);
                }

                return $reportId;
            });
        } catch (Throwable $exception) {
            if ($storedPaths !== []) {
                Storage::disk('local')->delete($storedPaths);
            }
            throw $exception;
        }
    }

    public function updateStatus(User $admin, int $reportId, string $status, ?string $response): stdClass
    {
        return app(FeedbackProcessingService::class)->updateAsAdmin($admin, $reportId, $status, $response);
    }

    private function ownedBookings(User $user): Builder
    {
        return BookingRecord::query()->toBase()
            ->join('trips', 'trips.id', '=', 'bookings.trip_id')
            ->join('routes', 'routes.id', '=', 'trips.route_id')
            ->leftJoin('buses', 'buses.id', '=', 'trips.bus_id')
            ->leftJoin('customers', 'customers.id', '=', 'bookings.customer_id')
            ->where(function (Builder $query) use ($user): void {
                $query->where('bookings.user_id', $user->id)
                    ->orWhere(function (Builder $guest) use ($user): void {
                        $guest->whereNull('bookings.user_id')->where('customers.user_id', $user->id);
                    });
            })
            ->select([
                'bookings.id', 'bookings.booking_code', 'trips.departure_time', 'trips.arrival_time',
                'routes.origin_city', 'routes.destination_city',
                'trips.bus_company_id as trip_company', 'routes.bus_company_id as route_company', 'buses.bus_company_id as bus_company',
            ]);
    }

    private function storeAttachment(int $reportId, UploadedFile $attachment, array &$storedPaths): void
    {
        $mimeType = $attachment->getMimeType();
        $extension = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => null,
        };
        if ($extension === null || @getimagesize($attachment->getRealPath()) === false) {
            throw ValidationException::withMessages(['attachments' => __('Profile::feedback.validation.attachment_image')]);
        }

        $path = 'customer-feedback/'.$reportId.'/'.Str::uuid().'.'.$extension;
        $storedPaths[] = $path;

        try {
            $stored = $attachment->storeAs(dirname($path), basename($path), 'local');
        } catch (Throwable $exception) {
            report($exception);
            throw ValidationException::withMessages(['attachments' => __('Profile::feedback.validation.attachment_store_failed')]);
        }

        if ($stored === false) {
            throw ValidationException::withMessages(['attachments' => __('Profile::feedback.validation.attachment_store_failed')]);
        }

        CustomerFeedbackAttachmentRecord::query()->toBase()->insert([
            'report_id'     => $reportId,
            'path'          => $path,
            'original_name' => mb_substr(basename(str_replace('\\', '/', $attachment->getClientOriginalName())), 0, 255),
            'mime_type'     => $mimeType,
            'size_bytes'    => $attachment->getSize(),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }
}
