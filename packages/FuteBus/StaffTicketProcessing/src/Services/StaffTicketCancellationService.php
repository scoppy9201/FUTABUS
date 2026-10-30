<?php

declare(strict_types=1);

namespace FuteBus\StaffTicketProcessing\Services;

use App\Models\User;
use Carbon\Carbon;
use FuteBus\BookingManagement\Models\BookedSeat as BookedSeatRecord;
use FuteBus\BookingManagement\Models\Booking as BookingRecord;
use FuteBus\Cancellation\Models\TicketCancellation as TicketCancellationRecord;
use FuteBus\Cancellation\Notifications\TicketCancelled;
use FuteBus\Cancellation\Services\CancellationPolicy;
use FuteBus\Notification\Services\CompanyNotificationService;
use FuteBus\Payment\Models\SePayTransaction as SePayTransactionRecord;
use FuteBus\Payment\Models\TicketRefund as TicketRefundRecord;
use FuteBus\Payment\Services\BookingPaymentResolver;
use FuteBus\Payment\Services\StaffCashRefundService;
use FuteBus\Payment\Services\TicketEmailService;
use FuteBus\TicketManagement\Models\Ticket as TicketRecord;
use FuteBus\TripManagement\Models\Trip as TripRecord;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StaffTicketCancellationService
{
    public function __construct(
        private readonly CancellationPolicy $policy,
        private readonly CompanyNotificationService $notifications,
        private readonly TicketEmailService $ticketEmails,
    ) {}

    public function owned(User $user, int $booking): Builder
    {
        abort_unless($user->is_active && $user->email_verified_at !== null && $user->isStaff()
            && $user->bus_company_id && $user->hasPermissionTo('ticket.view') && $user->hasPermissionTo('ticket.cancel'), 403);

        return TicketRecord::query()->toBase()->join('bookings', 'bookings.id', '=', 'tickets.booking_id')
            ->join('trips', 'trips.id', '=', 'tickets.trip_id')
            ->join('routes', 'routes.id', '=', 'trips.route_id')
            ->join('buses', 'buses.id', '=', 'trips.bus_id')
            ->join('bus_companies', 'bus_companies.id', '=', 'trips.bus_company_id')
            ->leftJoin('customers', 'customers.id', '=', 'bookings.customer_id')
            ->where('bookings.id', $booking)->where('trips.bus_company_id', $user->bus_company_id)
            ->select('tickets.*', 'bookings.booking_code', 'bookings.status as booking_status',
                'trips.departure_time', 'trips.arrival_time', 'trips.status as trip_status', 'routes.origin_city', 'routes.destination_city',
                'bookings.user_id as owner_user_id', 'customers.user_id as customer_user_id', 'bookings.notes',
                'bus_companies.name as company_name', 'buses.bus_type', 'buses.license_plate')
            ->selectSub(DB::table('ticket_exchanges')->select('original_departure_at')
                ->whereColumn('new_ticket_id', 'tickets.id')->where('status', 'completed')->limit(1), 'original_departure_at');
    }

    public function quote(User $user, int $booking, int $ticket): array
    {
        $record = $this->owned($user, $booking)->where('tickets.id', $ticket)->first();
        abort_if($record === null, 404);
        $payment = $this->payment($booking);

        return ['ticket'         => $record, 'quote' => $this->policy->quote($record), 'method' => $payment->method,
            'can_request_refund' => (bool) ($record->owner_user_id ?: $record->customer_user_id),
            'cash'               => $payment->method === 'cash', 'can_refund_cash' => $user->hasPermissionTo('payment.refund')];
    }

    public function cancel(User $user, int $booking, array $input): object
    {
        $candidate = $this->owned($user, $booking)->where('tickets.id', $input['ticket_id'])->first();
        abort_if($candidate === null, 404);

        return DB::transaction(function () use ($user, $booking, $input, $candidate): object {
            TripRecord::query()->toBase()->where('id', $candidate->trip_id)->lockForUpdate()->first();
            BookingRecord::query()->toBase()->where('id', $booking)->lockForUpdate()->first();
            TicketRecord::query()->toBase()->where('id', $candidate->id)->lockForUpdate()->first();
            $existing = TicketCancellationRecord::query()->toBase()->where('ticket_id', $candidate->id)->first();
            if ($existing !== null) {
                abort_unless($existing->actor_id !== null, 409, __('StaffTicketProcessing::cancellation.existing_request'));

                return $existing;
            }
            $data = $this->quote($user, $booking, (int) $candidate->id);
            $quote = $data['quote'];
            if ((int) $input['expected_refund'] !== $quote['refund']) {
                throw ValidationException::withMessages(['ticket' => __('Cancellation::cancellation.quote_changed')]);
            }
            $payment = $this->payment($booking, true);
            $cash = ($input['refund_method'] ?? ($payment->method === 'cash' ? 'cash' : 'request')) === 'cash';
            if (! $cash && ($payment->method === 'cash' || ! $data['can_request_refund'])) {
                throw ValidationException::withMessages(['refund_method' => __('StaffTicketProcessing::cancellation.online_owner_required')]);
            }
            if ($cash) {
                abort_unless($user->hasPermissionTo('payment.refund'), 403, __('StaffTicketProcessing::cancellation.cash_permission'));
                if (! filter_var($input['cash_paid'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                    throw ValidationException::withMessages(['cash_paid' => __('StaffTicketProcessing::cancellation.cash_confirm')]);
                }
            }
            $reserved = TicketRefundRecord::query()->toBase()->where('payment_id', $payment->id)->sum('amount');
            if ($reserved + $quote['refund'] > (int) $payment->amount) {
                throw ValidationException::withMessages(['ticket' => __('Cancellation::cancellation.payment_unavailable')]);
            }
            $reference = (string) Str::uuid();
            $id = TicketCancellationRecord::query()->toBase()->insertGetId([
                'reference'      => $reference, 'ticket_id' => $candidate->id,
                'user_id'        => $data['ticket']->owner_user_id ?: $data['ticket']->customer_user_id, 'actor_id' => $user->id,
                'reason'         => $input['reason'], 'note' => $input['note'] ?? null,
                'ticket_amount'  => $quote['amount'], 'fee_percent' => $quote['fee_percent'],
                'fee_amount'     => $quote['fee'], 'refund_amount' => $quote['refund'],
                'policy_version' => $quote['policy_version'], 'departure_at' => $data['ticket']->departure_time,
                'created_at'     => now(), 'updated_at' => now(),
            ]);
            TicketRefundRecord::query()->toBase()->insert([
                'cancellation_id' => $id, 'payment_id' => $payment->id, 'transaction_id' => $payment->transaction_id ?: $payment->payment_code,
                'idempotency_key' => 'futa-cancel-'.$reference, 'amount' => $quote['refund'],
                'environment'     => SePayTransactionRecord::query()->toBase()->where('sepay_id', $payment->transaction_id)->value('environment')
                    ?? config('services.sepay.environment', 'live'),
                'processing_mode' => 'manual',
                'staff_note'      => $cash ? 'staff-cash-refund-selected' : null,
                'status'          => 'refund_pending', 'created_at' => now(), 'updated_at' => now(),
            ]);
            TicketRecord::query()->toBase()->where('id', $candidate->id)->update(['status' => 'cancelled', 'updated_at' => now()]);
            DB::table('ticket_exchanges')->where('old_ticket_id', $candidate->id)->where('status', 'held')
                ->update(['status' => 'released', 'updated_at' => now()]);
            BookedSeatRecord::query()->toBase()->where('booking_id', $booking)->where('trip_id', $candidate->trip_id)
                ->where('seat_layout_id', $candidate->seat_layout_id)->delete();
            $remaining = TicketRecord::query()->toBase()->where('booking_id', $booking)->whereIn('status', ['active', 'used']);
            BookingRecord::query()->toBase()->where('id', $booking)->update(['seat_count' => (clone $remaining)->count(),
                'total_amount'                                                            => (clone $remaining)->sum('price'), 'updated_at' => now()]);
            if (! TicketRecord::query()->toBase()->where('booking_id', $booking)->whereIn('status', ['active', 'used'])->exists()) {
                BookingRecord::query()->toBase()->where('id', $booking)->update(['status' => 'cancelled', 'updated_at' => now()]);
            }
            $owner = $data['ticket']->owner_user_id ?: $data['ticket']->customer_user_id;
            DB::afterCommit(function () use ($owner, $reference, $candidate, $quote, $booking): void {
                if ($owner) {
                    User::find($owner)?->notify(new TicketCancelled([
                        'reference' => $reference, 'ticket_code' => $candidate->ticket_code,
                        'refund'    => $quote['refund'], 'booking_id' => $booking,
                    ]));
                }
            });

            $this->notifyCompany($candidate, $quote['refund'], $booking);
            $this->ticketEmails->cancellationRequested($id);
            if ($cash) {
                app(StaffCashRefundService::class)->complete($user, $id);
            }

            return TicketCancellationRecord::query()->toBase()->where('id', $id)->first();
        }, 3);
    }

    private function notifyCompany(object $ticket, int $refund, int $booking): void
    {
        $companyId = TripRecord::query()->toBase()->where('id', $ticket->trip_id)->value('bus_company_id');
        if ($companyId === null) {
            return;
        }

        DB::afterCommit(function () use ($companyId, $ticket, $refund, $booking): void {
            $this->notifications->notifyManagers((int) $companyId, [
                'title'   => __('Notification::app.booking_cancelled_title'),
                'summary' => __('Notification::app.booking_cancelled_summary', ['customer' => $ticket->passenger_name]),
                'url'     => route('bookings.show', ['kind' => 'booking', 'record' => $booking], false),
                'message' => __('Notification::app.booking_cancelled_message', [
                    'booking'   => $ticket->booking_code,
                    'ticket'    => $ticket->ticket_code,
                    'route'     => $ticket->origin_city.' - '.$ticket->destination_city,
                    'trip_id'   => $ticket->trip_id,
                    'departure' => Carbon::parse($ticket->departure_time)->format('d/m/Y H:i'),
                    'refund'    => number_format($refund, 0, ',', '.'),
                ]),
            ]);
        });
    }

    private function payment(int $booking, bool $lock = false): object
    {
        return app(BookingPaymentResolver::class)->verified($booking, $lock);
    }
}
