<table class="w-full min-w-260 text-left text-sm">
    <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-600">
        <tr>
            @foreach(['ticket_pair', 'customer', 'old_trip', 'new_trip', 'actor', 'action'] as $key)
                <th scope="col" @class(['px-4 py-4', 'text-center' => $key === 'action'])>{{ __('Payment::exchange.'.$key) }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
        @forelse($records as $exchange)
            <tr class="transition hover:bg-slate-50" data-exchange-history="{{ $exchange->id }}">
                <td class="px-4 py-4">
                    <p class="text-xs text-slate-500">{{ $exchange->old_code }}</p>
                    <p class="mt-1 font-bold text-futa-orange">{{ $exchange->new_code }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $exchange->origin_city }} → {{ $exchange->destination_city }}</p>
                </td>
                <td class="px-4 py-4">
                    <p class="font-medium text-slate-700">{{ $exchange->passenger_name }}</p>
                    @if($exchange->passenger_phone)<p class="mt-1 text-xs text-slate-500">{{ $exchange->passenger_phone }}</p>@endif
                </td>
                <td class="px-4 py-4">
                    <p>{{ \Illuminate\Support\Carbon::parse($exchange->old_departure)->format('H:i d/m/Y') }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $exchange->old_seat }}</p>
                </td>
                <td class="px-4 py-4">
                    <p>{{ \Illuminate\Support\Carbon::parse($exchange->new_departure)->format('H:i d/m/Y') }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $exchange->new_seat }}</p>
                </td>
                <td class="px-4 py-4">
                    <p class="font-medium text-slate-700">{{ $exchange->actor_name }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ \Illuminate\Support\Carbon::parse($exchange->completed_at)->format('H:i d/m/Y') }}</p>
                    <p class="mt-1 text-xs text-emerald-700">{{ __('Payment::exchange.fee') }}: {{ number_format($exchange->fee, 0, ',', '.') }} ₫</p>
                </td>
                <td class="px-4 py-4 text-center">
                    <a href="{{ url()->current().'?'.http_build_query(['tab' => 'tickets', 'code' => $exchange->new_code]) }}"
                        class="inline-flex items-center gap-2 whitespace-nowrap rounded-xl border border-orange-200 px-3 py-2 text-xs font-semibold text-futa-orange hover:bg-futa-orange-soft">
                        <x-heroicon-o-eye class="size-4" aria-hidden="true" />{{ __('Payment::exchange.view_ticket') }}
                    </a>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="p-8"><x-core::empty-state :title="__('Payment::exchange.no_history')" /></td></tr>
        @endforelse
    </tbody>
</table>
