<?php

declare(strict_types=1);

namespace FuteBus\Payment\Services;

use FuteBus\Cancellation\Models\TicketCancellation as TicketCancellationRecord;
use FuteBus\Payment\Models\TicketRefund as TicketRefundRecord;
use FuteBus\TicketManagement\Models\Ticket as TicketRecord;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SePayRefundService
{
    public function process(int $id): void
    {
        $refund = TicketRefundRecord::query()->toBase()->where('id', $id)->first();
        if ($refund === null || $refund->processing_mode !== 'automatic'
            || config('services.sepay.refund_mode', 'manual') !== 'automatic'
            || in_array($refund->status, ['succeeded', 'failed'], true)
            || ! config('services.sepay.refund_enabled') || ! config('services.sepay.refund_token')
            || ! config('services.sepay.refund_bank_id')
            || $refund->environment !== config('services.sepay.refund_environment')) {
            return;
        }
        $base = match ($refund->environment) {
            'sandbox' => 'https://userapi-sandbox.sepay.vn/v2',
            'live'    => 'https://userapi.sepay.vn/v2',
            default   => null,
        };
        if ($base === null) {
            return;
        }
        try {
            $client = Http::withToken(config('services.sepay.refund_token'))->acceptJson()->timeout(12)->connectTimeout(5);
            $response = $refund->provider_refund_id
                ? $client->get($base.'/bank-accounts/'.rawurlencode(config('services.sepay.refund_bank_id'))
                    .'/refunds/'.rawurlencode($refund->provider_refund_id))
                : $client->withHeaders(['X-Idempotency-Key' => $refund->idempotency_key])
                    ->post($base.'/transactions/'.rawurlencode($refund->transaction_id).'/refund', ['refund_amount' => $refund->amount]);
        } catch (ConnectionException) {
            TicketRefundRecord::query()->toBase()->where('id', $id)->whereNotIn('status', ['succeeded', 'failed'])
                ->update(['error_code' => 'connection_unknown', 'updated_at' => now()]);

            return;
        }
        $data = $response->json('data');
        if (in_array($response->status(), [400, 401, 403, 404, 409, 422], true)) {
            TicketRefundRecord::query()->toBase()->where('id', $id)->whereNotIn('status', ['succeeded', 'failed'])
                ->update(['status' => 'needs_review', 'error_code' => 'provider_rejected', 'updated_at' => now()]);

            return;
        }
        if (! $response->successful() || $response->json('status') !== 'success' || ! is_array($data)
            || (string) ($data['transaction_id'] ?? '') !== $refund->transaction_id
            || ($data['amount'] ?? null) !== (int) $refund->amount || ($data['currency'] ?? null) !== 'VND'
            || ! is_string($data['refund_id'] ?? null) || $data['refund_id'] === '' || strlen($data['refund_id']) > 255
            || ($refund->provider_refund_id && $data['refund_id'] !== $refund->provider_refund_id)
            || ! in_array($data['status'] ?? null, ['pending', 'succeeded', 'failed'], true)) {
            TicketRefundRecord::query()->toBase()->where('id', $id)->whereNotIn('status', ['succeeded', 'failed'])
                ->update(['error_code' => 'provider_unverified', 'updated_at' => now()]);

            return;
        }
        DB::transaction(function () use ($id, $data): void {
            $current = TicketRefundRecord::query()->toBase()->where('id', $id)->lockForUpdate()->first();
            if ($current->status === 'succeeded') {
                return;
            }
            TicketRefundRecord::query()->toBase()->where('id', $id)->update([
                'provider_refund_id' => $data['refund_id'], 'status' => $data['status'], 'error_code' => null,
                'succeeded_at'       => $data['status'] === 'succeeded' ? now() : null, 'updated_at' => now(),
            ]);
            if ($data['status'] === 'succeeded') {
                $ticket = TicketCancellationRecord::query()->toBase()->where('id', $current->cancellation_id)->value('ticket_id');
                TicketRecord::query()->toBase()->where('id', $ticket)->where('status', 'cancelled')
                    ->update(['status' => 'refunded', 'updated_at' => now()]);
                app(TicketEmailService::class)->refundCompleted($id);
            }
        });
    }
}
