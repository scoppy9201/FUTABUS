<?php

declare(strict_types=1);

namespace FuteBus\Profile\Services;

use App\Models\User;
use FuteBus\Profile\Models\BookingReview as BookingReviewRecord;
use Illuminate\Support\Facades\DB;

class TicketReviewService
{
    public function __construct(private TicketHistoryService $history) {}

    public function submit(User $user, int $bookingId, int $rating, ?string $comment): void
    {
        DB::transaction(function () use ($user, $bookingId, $rating, $comment): void {
            $detail = $this->history->detailForUser($user, $bookingId);
            abort_if($detail === null, 404);

            abort_unless($detail['canReview'], 403);

            BookingReviewRecord::query()->toBase()->upsert([[
                'booking_id' => $bookingId,
                'user_id'    => $user->id,
                'rating'     => $rating,
                'comment'    => $comment,
                'created_at' => now(),
                'updated_at' => now(),
            ]], ['booking_id'], ['user_id', 'rating', 'comment', 'updated_at']);
        });
    }
}
