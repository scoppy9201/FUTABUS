<table class="w-full min-w-240 text-left text-sm">
    <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-600">
        <tr>
            @foreach(['ticket_booking', 'customer', 'journey', 'fare', 'eligibility', 'action'] as $key)
                <th scope="col" @class(['px-4 py-4', 'text-center' => $key === 'action'])>{{ __('Payment::exchange.'.$key) }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
        @forelse($records as $ticket)
            <tr class="transition hover:bg-slate-50" data-exchange-ticket="{{ $ticket->id }}">
                <td class="px-4 py-4">
                    <p class="font-bold text-slate-900">{{ $ticket->ticket_code }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $ticket->booking_code }}</p>
                </td>
                <td class="px-4 py-4">
                    <p class="font-medium text-slate-700">{{ $ticket->passenger_name }}</p>
                    @if($ticket->passenger_phone)<p class="mt-1 text-xs text-slate-500">{{ $ticket->passenger_phone }}</p>@endif
                </td>
                <td class="px-4 py-4">
                    <p class="font-semibold text-slate-700">{{ $ticket->origin_city }} → {{ $ticket->destination_city }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ \Illuminate\Support\Carbon::parse($ticket->departure_time)->format('H:i d/m/Y') }} · {{ $ticket->seat_code }}</p>
                </td>
                <td class="whitespace-nowrap px-4 py-4 font-bold text-slate-900">{{ number_format($ticket->price, 0, ',', '.') }} ₫</td>
                <td class="max-w-64 px-4 py-4">
                    <span @class(['inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                        'bg-emerald-50 text-emerald-700' => $ticket->eligible, 'bg-slate-100 text-slate-600' => !$ticket->eligible])>
                        {{ __('Payment::exchange.'.($ticket->eligible ? 'eligible' : 'blocked')) }}
                    </span>
                    @if(!$ticket->eligible)<p class="mt-2 text-xs leading-5 text-slate-500">{{ $ticket->blocked_reason }}</p>@endif
                </td>
                <td class="px-4 py-4 text-center">
                    <a href="{{ route('exchange.show', $ticket->booking_id) }}" data-ticket-exchange data-no-loader aria-haspopup="dialog"
                        class="inline-flex items-center gap-2 whitespace-nowrap rounded-xl border border-orange-200 px-3 py-2 text-xs font-semibold text-futa-orange hover:bg-futa-orange-soft">
                        <x-heroicon-o-arrows-right-left class="size-4" aria-hidden="true" />{{ __('Payment::exchange.'.($ticket->eligible ? 'start' : 'conditions')) }}
                    </a>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="p-8">
                <x-core::empty-state :title="__('Payment::exchange.'.($code !== '' ? 'not_found' : 'no_upcoming'))"
                    :description="__('Payment::exchange.search_hint')" />
            </td></tr>
        @endforelse
    </tbody>
</table>
