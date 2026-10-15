@if($exchanges->isNotEmpty())
    <section class="rounded-xl border border-orange-100 bg-futa-orange-soft/40 p-4 text-sm">
        <h3 class="font-bold text-futa-orange">{{ __('Payment::exchange.audit') }}</h3>
        <ol class="mt-3 space-y-4">
            @foreach($exchanges as $exchange)
                <li class="space-y-1 text-xs leading-5 text-slate-600">
                    <p class="break-all font-semibold text-slate-900">{{ $exchange->old_code }} → {{ $exchange->new_code }}</p>
                    <p>{{ \Illuminate\Support\Carbon::parse($exchange->completed_at)->format('H:i d/m/Y') }}</p>
                    @if($showActor ?? false)<p>{{ __('Payment::exchange.actor') }}: {{ $exchange->actor_name }}</p>@endif
                    <p>{{ __('Payment::exchange.fee') }}: {{ number_format($exchange->fee, 0, ',', '.') }} ₫</p>
                </li>
            @endforeach
        </ol>
    </section>
@endif
