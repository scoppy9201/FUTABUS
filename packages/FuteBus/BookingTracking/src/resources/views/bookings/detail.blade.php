@php
    $date = fn ($value) => $value ? \Illuminate\Support\Carbon::parse($value)->format('d/m/Y H:i') : '—';
    $money = fn ($value) => $value === null ? '—' : number_format($value, 0, ',', '.').' ₫';
@endphp
<div class="space-y-6 p-6 text-left text-sm">
    <section class="rounded-xl border border-slate-200 bg-slate-50 p-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div><p class="text-xs text-slate-500">{{ __('BookingTracking::bookings.'.($record->kind === 'booking' ? 'booking' : 'unissued')) }}</p>
                <h3 class="mt-1 wrap-break-word text-lg font-bold text-slate-950">{{ $record->code }}</h3>
            </div>
            @include('BookingTracking::bookings.status', ['status' => $record->status])
        </div>
        <p class="mt-4 font-semibold">{{ $record->origin_city }} → {{ $record->destination_city }}</p>
        <dl class="mt-4 grid grid-cols-2 gap-4">
            @foreach(['departure' => $date($record->departure_time), 'created' => $date($record->created_at),
                'seats' => $record->seat_count, 'amount' => $money($record->amount)] as $key => $value)
                <div><dt class="text-xs text-slate-500">{{ __('BookingTracking::bookings.'.$key) }}</dt><dd class="mt-1 font-semibold">{{ $value }}</dd></div>
            @endforeach
        </dl>
        <div class="mt-4 flex items-center justify-between gap-3">
            <span class="text-xs text-slate-500">{{ __('BookingTracking::bookings.trip_status') }}</span>
            @include('BookingTracking::bookings.status', ['status' => $record->trip_status, 'group' => 'trip_statuses'])
        </div>
        @if($record->expires_at)<p class="mt-4 text-xs text-slate-500">{{ __('BookingTracking::bookings.expires') }}: {{ $date($record->expires_at) }}</p>@endif
        @if($record->status === 'needs_review')<p class="mt-4 rounded-lg bg-amber-50 p-3 text-amber-800">{{ __('BookingTracking::bookings.needs_review_hint') }}</p>@endif
    </section>
    <section>
        <h3 class="flex items-center gap-2 font-bold text-slate-950"><x-heroicon-o-user class="size-5 text-futa-orange" />{{ __('BookingTracking::bookings.customer') }}</h3>
        <dl class="mt-4 space-y-3">
            @foreach(['customer' => $record->customer_name, 'phone' => $record->customer_phone, 'email' => $record->customer_email] as $key => $value)
                <div class="grid grid-cols-[8rem_minmax(0,1fr)] gap-3"><dt class="text-slate-500">{{ __('BookingTracking::bookings.'.$key) }}</dt><dd class="wrap-break-word font-medium">{{ $value ?: '—' }}</dd></div>
            @endforeach
        </dl>
    </section>
    @if($stops)
        <section class="space-y-3 border-t border-slate-100 pt-5">
            @foreach(['pickup', 'dropoff'] as $key)
                <div class="grid grid-cols-[8rem_minmax(0,1fr)] gap-3">
                    <span class="text-slate-500">{{ __('BookingTracking::bookings.'.$key) }}</span>
                    <div class="wrap-break-word"><p class="font-medium">{{ $stops[$key]['name'] ?? '—' }}</p>
                        @if(!empty($stops[$key]['address']))<p class="mt-1 text-xs text-slate-500">{{ $stops[$key]['address'] }}</p>@endif
                    </div>
                </div>
            @endforeach
        </section>
    @endif
    <section class="border-t border-slate-100 pt-5">
        <h3 class="flex items-center gap-2 font-bold text-slate-950"><x-heroicon-o-ticket class="size-5 text-futa-orange" />{{ __('BookingTracking::bookings.tickets') }}</h3>
        <div class="mt-4 space-y-3">
            @forelse($tickets as $ticket)
                <article class="rounded-xl border border-slate-200 p-4">
                    <div class="flex flex-wrap justify-between gap-2"><p class="font-semibold">{{ $ticket->ticket_code }}</p>
                        @include('BookingTracking::bookings.status', ['status' => $ticket->status, 'group' => 'ticket_statuses'])
                    </div>
                    <p class="mt-2">{{ __('BookingTracking::bookings.seat') }} {{ $ticket->seat_code ?: '—' }} · {{ $money($ticket->price) }}</p>
                    <p class="mt-1 text-slate-500">{{ $ticket->passenger_name }} · {{ $ticket->passenger_phone ?: '—' }}</p>
                    @if($ticket->used_at)<p class="mt-2 text-xs text-slate-500">{{ __('BookingTracking::bookings.ticket_statuses.used') }}: {{ $date($ticket->used_at) }}</p>@endif
                </article>
            @empty
                @if(!empty($record->seats))<p class="mb-3 text-slate-500">{{ __('BookingTracking::bookings.seat') }}: {{ implode(', ', $record->seats) }}</p>@endif
                <x-core::empty-state inline :title="__('BookingTracking::bookings.no_tickets')" />
            @endforelse
        </div>
    </section>
    <section class="border-t border-slate-100 pt-5">
        <h3 class="flex items-center gap-2 font-bold text-slate-950"><x-heroicon-o-credit-card class="size-5 text-futa-orange" />{{ __('BookingTracking::bookings.payments') }}</h3>
        <div class="mt-4 space-y-3">
            @forelse($payments as $payment)
                <article class="rounded-xl border border-slate-200 p-4">
                    <div class="flex flex-wrap justify-between gap-2"><p class="font-semibold">{{ $payment->payment_code }}</p>
                        @include('BookingTracking::bookings.status', ['status' => $payment->status, 'group' => 'payment_statuses'])
                    </div>
                    <p class="mt-2 text-slate-500">{{ __('BookingTracking::bookings.methods')[$payment->method] ?? __('BookingTracking::bookings.unknown') }}</p>
                    @if($payment->transaction_id)
                        <p class="mt-2 wrap-break-word text-xs text-slate-500">{{ __('BookingTracking::bookings.transaction') }}: {{ $payment->transaction_id }}</p>
                    @endif
                    <p class="mt-2">{{ __('BookingTracking::bookings.payment_amount') }}: <strong>{{ $money($payment->amount) }}</strong></p>
                    <p class="mt-1 text-xs text-slate-500">{{ __('BookingTracking::bookings.paid_at') }}: {{ $date($payment->paid_at) }}</p>
                </article>
            @empty<x-core::empty-state inline :title="__('BookingTracking::bookings.no_payments')" />@endforelse
        </div>
        @if($payments->isNotEmpty())<p class="mt-3 text-xs leading-5 text-slate-500">{{ __('BookingTracking::bookings.joint_payment') }}</p>@endif
    </section>
    <section class="border-t border-slate-100 pt-5">
        <h3 class="flex items-center gap-2 font-bold text-slate-950"><x-heroicon-o-receipt-refund class="size-5 text-futa-orange" />{{ __('BookingTracking::bookings.refunds') }}</h3>
        <div class="mt-4 space-y-3">
            @forelse($refunds as $refund)
                <article class="rounded-xl border border-slate-200 p-4">
                    <p class="wrap-break-word font-semibold">{{ $refund->reference }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $date($refund->created_at) }}</p>
                    <p class="mt-3 text-slate-500">{{ __('BookingTracking::bookings.reason') }}:
                        {{ __('Cancellation::cancellation.reasons')[$refund->reason] ?? __('BookingTracking::bookings.unknown') }}</p>
                    @if($refund->note)<p class="mt-2 whitespace-pre-wrap wrap-break-word text-slate-500">{{ $refund->note }}</p>@endif
                    <dl class="mt-3 space-y-2">
                        @foreach(['fee' => $money($refund->fee_amount), 'refund_amount' => $money($refund->refund_amount)] as $key => $value)
                            <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('BookingTracking::bookings.'.$key) }}</dt><dd class="font-semibold">{{ $value }}</dd></div>
                        @endforeach
                    </dl>
                    <div class="mt-3">@if($refund->status)@include('Payment::refunds.status', ['status' => $refund->status])@else<span class="text-slate-500">{{ __('BookingTracking::bookings.unknown') }}</span>@endif</div>
                    @if($refund->bank_paid_at)<p class="mt-2 text-xs text-slate-500">{{ __('BookingTracking::bookings.paid_at') }}: {{ $date($refund->bank_paid_at) }}</p>@endif
                </article>
            @empty<x-core::empty-state inline :title="__('BookingTracking::bookings.no_refunds')" />@endforelse
        </div>
    </section>
</div>
