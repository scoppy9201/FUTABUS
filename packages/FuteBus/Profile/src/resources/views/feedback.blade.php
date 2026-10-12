@extends('core::layouts.home')

@section('title', __('Profile::feedback.title'))

@section('content')
    @php
        $filters = $filters ?? [];
        $hasFilters = filled($filters['status'] ?? null) || filled($filters['search'] ?? null);
        $hasFormErrors = collect($errors->keys())->contains(static fn (string $key): bool =>
            array_key_exists(explode('.', $key)[0], __('Profile::feedback.fields'))
        );
    @endphp
    <div class="home-page min-h-screen bg-white" data-feedback-page>
        @include('core::partials.home.navbar')
        <main class="mx-auto grid w-full max-w-282 items-start gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[255px_minmax(0,1fr)] lg:items-stretch lg:px-0">
            @include('Profile::partials.account-sidebar')
            <section class="min-w-0 lg:flex lg:h-134 lg:flex-col" aria-labelledby="feedback-title">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 id="feedback-title" class="text-3xl font-semibold text-gray-950">{{ __('Profile::feedback.title') }}</h1>
                        <p class="mt-2 max-w-lg text-base text-slate-600">{{ __('Profile::feedback.description') }}</p>
                    </div>
                    <button type="button" data-feedback-open aria-haspopup="dialog" aria-controls="feedback-create-dialog"
                        class="inline-flex min-h-11 items-center gap-2 rounded-full bg-futa-orange px-5 text-sm font-bold text-white shadow-sm hover:bg-futa-orange-dark">
                        <x-heroicon-o-plus class="size-5" aria-hidden="true" />{{ __('Profile::feedback.create') }}
                    </button>
                </div>
                @if(session('status'))
                    <div hidden data-notice-on-load data-notice-tone="success" data-notice-title="{{ __('Profile::feedback.title') }}"
                        data-notice-message="{{ session('status') }}"></div>
                @endif

                <form action="{{ route('profile.feedback.index') }}" method="get"
                    class="mt-7 flex flex-wrap gap-3 rounded-2xl border border-gray-200 bg-futa-orange-soft/40 p-4">
                    <div class="relative min-w-48 flex-1">
                        <label class="sr-only" for="feedback-search">{{ __('Profile::feedback.search') }}</label>
                        <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-3 size-5 text-futa-orange" aria-hidden="true" />
                        <input id="feedback-search" name="search" maxlength="150" value="{{ $filters['search'] ?? '' }}"
                            placeholder="{{ __('Profile::feedback.search_placeholder') }}"
                            class="h-11 w-full rounded-lg border border-gray-200 bg-white pl-10 pr-3 text-sm outline-none focus:border-futa-orange">
                    </div>
                    <label class="sr-only" for="feedback-status">{{ __('Profile::feedback.columns.status') }}</label>
                    <select id="feedback-status" name="status"
                        class="h-11 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm outline-none focus:border-futa-orange sm:w-44">
                        <option value="">{{ __('Profile::feedback.all_statuses') }}</option>
                        @foreach(__('Profile::feedback.status') as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button class="h-11 rounded-full border border-futa-orange bg-white px-6 text-sm font-semibold text-futa-orange hover:bg-futa-orange-soft">
                        {{ __('Profile::feedback.search') }}
                    </button>
                </form>

                <section class="mt-5 flex min-h-0 flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm lg:flex-1" aria-labelledby="feedback-history-title">
                    <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-4">
                        <h2 id="feedback-history-title" class="text-sm font-bold text-futa-green">{{ __('Profile::feedback.results', ['count' => $reports->total()]) }}</h2>
                        @if($hasFilters)
                            <a href="{{ route('profile.feedback.index') }}" class="text-sm font-semibold text-futa-orange hover:underline">{{ __('Profile::feedback.clear_filters') }}</a>
                        @endif
                    </div>
                    <div class="ticket-history-scroll h-96 overflow-auto lg:min-h-0 lg:h-auto lg:flex-1" tabindex="0" aria-label="{{ __('Profile::feedback.history_title') }}">
                        <table class="w-full min-w-160 text-left text-sm">
                            <caption class="sr-only">{{ __('Profile::feedback.history_title') }}</caption>
                            <thead class="sticky top-0 z-10 bg-futa-orange-soft text-gray-700">
                                <tr>
                                    @foreach(__('Profile::feedback.columns') as $label)
                                        <th scope="col" class="whitespace-nowrap px-5 py-3.5 font-semibold">{{ $label }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($reports as $report)
                                    @php
                                        $statusColor = match ($report->status) {
                                            'resolved' => 'bg-green-50 text-futa-green',
                                            'reviewing' => 'bg-blue-50 text-blue-700',
                                            default => 'bg-futa-orange-soft text-futa-orange',
                                        };
                                    @endphp
                                    <tr class="align-top hover:bg-gray-50/70">
                                        <td class="max-w-80 px-5 py-4">
                                            <p class="text-xs font-bold text-futa-orange">{{ __('Profile::feedback.reference', ['id' => str_pad((string) $report->id, 6, '0', STR_PAD_LEFT)]) }}</p>
                                            <p class="mt-1 wrap-break-word font-semibold text-gray-950">{{ $report->subject }}</p>
                                            <p class="mt-1 text-xs text-slate-500">{{ __('Profile::feedback.categories.'.$report->category) }} · {{ __('Profile::feedback.urgencies.'.$report->urgency) }}</p>
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ \Illuminate\Support\Carbon::parse($report->created_at)->format('d/m/Y H:i') }}</td>
                                        <td class="whitespace-nowrap px-5 py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusColor }}">{{ __('Profile::feedback.status.'.$report->status) }}</span></td>
                                        <td class="whitespace-nowrap px-5 py-4">
                                            <a href="{{ route('profile.feedback.show', $report->id) }}" data-feedback-detail-link data-no-loader
                                                aria-haspopup="dialog" aria-controls="feedback-detail-dialog"
                                                class="font-semibold text-futa-orange hover:underline">{{ __('Profile::feedback.view_content') }}</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-5 py-12 text-center">
                                        <span class="mx-auto grid size-14 place-items-center rounded-full bg-futa-orange-soft text-futa-orange"><x-heroicon-o-chat-bubble-left-right class="size-7" aria-hidden="true" /></span>
                                        <p class="mt-4 text-lg font-semibold text-gray-950">{{ __('Profile::feedback.'.($hasFilters ? 'filtered_empty' : 'empty')) }}</p>
                                        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">{{ __('Profile::feedback.'.($hasFilters ? 'filtered_hint' : 'empty_hint')) }}</p>
                                    </td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
                @if($reports->hasPages())
                    <div class="mt-5">{{ $reports->links() }}</div>
                @endif
            </section>
        </main>

        <dialog id="feedback-create-dialog" data-feedback-create @if($hasFormErrors) data-open-on-load @endif
            aria-labelledby="feedback-create-title"
            class="m-auto max-h-[calc(100dvh-1.5rem)] w-[min(64rem,calc(100vw-1.5rem))] overflow-hidden rounded-2xl border border-gray-200 bg-white p-0 shadow-2xl backdrop:bg-black/60 open:flex open:flex-col">
            <header class="flex shrink-0 items-start justify-between gap-4 border-b border-gray-100 px-5 py-4 sm:px-6">
                <div>
                    <h2 id="feedback-create-title" class="text-xl font-bold text-gray-950">{{ __('Profile::feedback.form_title') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Profile::feedback.form_hint') }}</p>
                </div>
                <button type="button" data-feedback-close aria-label="{{ __('Profile::feedback.close') }}" class="grid size-9 shrink-0 place-items-center rounded-full text-slate-500 hover:bg-gray-100">
                    <x-heroicon-o-x-mark class="size-5" aria-hidden="true" />
                </button>
            </header>
            <div class="ticket-history-scroll grid min-h-0 flex-1 items-start gap-6 overflow-y-auto p-5 sm:p-6 lg:grid-cols-[minmax(0,1fr)_15rem]">
                @include('Profile::partials.feedback-form')
                <aside class="hidden space-y-4 lg:block" aria-label="{{ __('Profile::feedback.process_title') }}">
                    <div class="rounded-xl bg-futa-green p-4 text-white">
                        <p class="text-sm font-bold">{{ __('Profile::feedback.help_title') }}</p>
                        <a href="tel:19006067" class="mt-2 inline-flex items-center gap-2 text-2xl font-extrabold"><x-heroicon-o-phone class="size-5" aria-hidden="true" />1900 6067</a>
                        <p class="mt-2 text-xs leading-5 text-white/90">{{ __('Profile::feedback.help_text') }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-4">
                        <h3 class="text-sm font-bold text-gray-950">{{ __('Profile::feedback.process_title') }}</h3>
                        <ol class="mt-4 space-y-4">
                            @foreach(__('Profile::feedback.process_steps') as $label => $description)
                                <li class="flex items-start gap-2.5">
                                    <span class="grid size-6 shrink-0 place-items-center rounded-full bg-futa-orange-soft text-xs font-bold text-futa-orange">{{ $loop->iteration }}</span>
                                    <div><p class="text-xs font-bold text-gray-900">{{ $label }}</p><p class="mt-1 text-xs leading-5 text-slate-500">{{ $description }}</p></div>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                    <div class="rounded-xl border border-gray-200 p-4">
                        <h3 class="text-sm font-bold text-gray-950">{{ __('Profile::feedback.commitment_title') }}</h3>
                        <ul class="mt-3 space-y-2">
                            @foreach(__('Profile::feedback.commitments') as $commitment)
                                <li class="flex gap-2 text-xs leading-5 text-slate-600"><x-heroicon-o-check-circle class="mt-0.5 size-4 shrink-0 text-futa-green" aria-hidden="true" />{{ $commitment }}</li>
                            @endforeach
                        </ul>
                    </div>
                </aside>
            </div>
        </dialog>
        <dialog id="feedback-detail-dialog" data-feedback-detail aria-label="{{ __('Profile::feedback.detail_title') }}"
            class="ticket-history-scroll m-auto max-h-[calc(100dvh-1.5rem)] w-[min(48rem,calc(100vw-1.5rem))] overflow-y-auto rounded-2xl border border-gray-200 bg-white p-0 shadow-2xl backdrop:bg-black/60">
            <div data-feedback-detail-content></div>
        </dialog>
        @include('core::partials.home.footer')
    </div>
    @vite('packages/FuteBus/Profile/src/resources/js/app.js')
@endsection
