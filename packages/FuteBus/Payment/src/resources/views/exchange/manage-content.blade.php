<div class="mx-auto w-full space-y-6">
    <header><p class="text-sm font-bold uppercase tracking-[0.16em] text-futa-orange">FUTA Bus Lines</p>
        <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">{{ __('Payment::exchange.title') }}</h1>
        <p class="mt-2 text-sm text-slate-500">{{ __('Payment::exchange.manage_hint') }}</p></header>
    <nav aria-label="{{ __('Payment::exchange.title') }}" class="flex gap-2 rounded-2xl border border-slate-200 bg-white p-2 shadow-sm">
        @foreach(['tickets' => 'upcoming', 'history' => 'audit'] as $value => $label)
            <a href="{{ url()->current().'?'.http_build_query(['tab' => $value, 'code' => $code]) }}"
                @if($tab === $value) aria-current="page" @endif
                @class(['rounded-xl px-4 py-2.5 text-sm font-semibold', 'bg-futa-orange-soft text-futa-orange' => $tab === $value,
                    'text-slate-600 hover:bg-slate-50' => $tab !== $value])>{{ __('Payment::exchange.'.$label) }}</a>
        @endforeach
    </nav>
    <form method="get" class="flex flex-wrap gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <label class="min-w-48 flex-1">
            <span class="relative block">
            <x-heroicon-o-magnifying-glass class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400" aria-hidden="true" />
            <input name="code" maxlength="64" value="{{ old('code', $code) }}" aria-label="{{ __('Payment::exchange.search') }}"
                aria-describedby="exchange-code-error" @error('code') aria-invalid="true" @enderror
                placeholder="{{ __('Payment::exchange.search') }}"
                class="w-full rounded-xl border border-slate-200 py-3 pr-4 pl-10 text-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20">
            </span>
            @error('code')<span id="exchange-code-error" class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
        </label>
        <button class="rounded-xl bg-futa-orange px-5 py-3 text-sm font-bold text-white">{{ __('Payment::exchange.find') }}</button>
        @if($code !== '')
            <a href="{{ url()->current().'?'.http_build_query(['tab' => $tab]) }}"
                class="rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-600">{{ __('Payment::exchange.clear') }}</a>
        @endif
    </form>
    <section data-exchange-management="{{ $tab }}" class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <header class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-5 py-4">
            <h2 class="font-bold text-slate-900">{{ __('Payment::exchange.'.($tab === 'history' ? 'audit' : 'upcoming')) }}</h2>
            <span class="text-sm text-slate-500">{{ __('Payment::exchange.records', ['count' => $records->total()]) }}</span>
        </header>
        <div class="overflow-x-auto">
            @include('Payment::exchange.management-'.($tab === 'history' ? 'history' : 'tickets'))
        </div>
        <footer class="border-t border-slate-100 p-4"><x-core::data-pagination :paginator="$records" /></footer>
    </section>
</div>
@include('Payment::exchange.host')
