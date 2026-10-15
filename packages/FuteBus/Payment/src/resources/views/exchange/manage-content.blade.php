<div class="mx-auto w-full space-y-6">
    <header><h1 class="text-2xl font-extrabold text-slate-950">{{ __('Payment::exchange.title') }}</h1>
        <p class="mt-2 text-sm text-slate-500">{{ __('Payment::exchange.manage_hint') }}</p></header>
    <form method="get" class="flex flex-wrap gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <label class="relative min-w-48 flex-1">
            <x-heroicon-o-magnifying-glass class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400" aria-hidden="true" />
            <input name="code" maxlength="64" value="{{ $code }}" required aria-label="{{ __('Payment::exchange.search') }}"
                placeholder="{{ __('Payment::exchange.search') }}"
                class="w-full rounded-xl border border-slate-200 py-3 pr-4 pl-10 text-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20">
        </label>
        <button class="rounded-xl bg-futa-orange px-5 py-3 text-sm font-bold text-white">{{ __('Payment::exchange.find') }}</button>
    </form>
    @if($record)
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="break-all font-bold">{{ $record->booking_code }}</h2>
            <p class="mt-2 text-sm text-slate-600">{{ $record->origin_city }} → {{ $record->destination_city }}</p>
            <p class="mt-2 text-sm text-slate-600">{{ $record->ticket_code }} · {{ $record->seat_code }} · {{ $record->departure_time }}</p>
            <a href="{{ route('exchange.show', $record->booking_id) }}" data-ticket-exchange data-no-loader
                class="mt-4 inline-flex items-center gap-2 rounded-xl bg-futa-orange px-4 py-2.5 text-sm font-semibold text-white">
                <x-heroicon-o-arrows-right-left class="size-4" aria-hidden="true" />{{ __('Payment::exchange.title') }}
            </a>
        </section>
    @elseif($searched)
        <x-core::empty-state :title="__('Payment::exchange.not_found')" />
    @endif
</div>
@include('Payment::exchange.host')
