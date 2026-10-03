@extends('core::layouts.home')

@section('title', __('Profile::futapay.title'))

@section('content')
    <div class="home-page min-h-screen bg-white">
        @include('core::partials.home.navbar')

        <main class="mx-auto grid w-full max-w-282 gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[255px_minmax(0,1fr)] lg:px-0">
            @include('Profile::partials.account-sidebar')

            <section
                class="min-w-0"
                aria-labelledby="futapay-history-title"
                data-notice-on-load
                data-notice-title="{{ __('Profile::futapay.notice_title') }}"
                data-notice-message="{{ __('Profile::futapay.notice_message') }}"
            >
                <div class="flex items-center justify-between gap-5 rounded-3xl border border-gray-200 bg-orange-50/50 px-6 py-6 sm:px-8">
                    <div>
                        <p class="text-base font-medium text-gray-700">{{ __('Profile::futapay.balance') }}</p>
                        <p class="mt-1 text-5xl font-semibold leading-none text-[#ef5222]">0 ₫</p>
                    </div>
                    <div class="flex shrink-0 flex-col items-center gap-2 text-center">
                        <span class="grid size-15 place-items-center rounded-full bg-sky-400 text-white">
                            <x-heroicon-o-clock class="size-9" />
                        </span>
                        <span class="text-sm font-semibold text-gray-900">{{ __('Profile::futapay.transactions') }}</span>
                    </div>
                </div>

                <h1 id="futapay-history-title" class="mt-8 text-3xl font-semibold text-gray-950">
                    {{ __('Profile::futapay.history') }}
                </h1>

                <div class="mt-5 flex flex-wrap items-end gap-4">
                    <div data-futapay-date-range class="relative min-w-0 flex-1 sm:max-w-76">
                        <span id="futapay-period-label" class="mb-1.5 block text-sm font-semibold text-gray-900">{{ __('Profile::futapay.period') }}</span>
                        <button
                            type="button"
                            data-range-toggle
                            aria-labelledby="futapay-period-label"
                            aria-haspopup="dialog"
                            aria-controls="futapay-range-calendar"
                            aria-expanded="false"
                            class="flex h-11 w-full items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 text-sm font-medium text-gray-900 hover:border-[#ef5222] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#ef5222]"
                        >
                            <span data-range-start-label class="min-w-0 flex-1 truncate text-slate-400">{{ __('Profile::futapay.from_date') }}</span>
                            <span aria-hidden="true" class="shrink-0 text-slate-400">→</span>
                            <span data-range-end-label class="min-w-0 flex-1 truncate text-slate-400">{{ __('Profile::futapay.to_date') }}</span>
                            <x-heroicon-o-calendar-days class="size-5 shrink-0 text-slate-400" />
                        </button>
                        <div
                            id="futapay-range-calendar"
                            data-range-calendar
                            hidden
                            role="dialog"
                            aria-label="{{ __('Profile::futapay.period') }}"
                            class="absolute left-0 top-full z-50 mt-3 max-h-[calc(100dvh-8rem)] w-[min(44rem,calc(100vw-2rem))] overflow-y-auto rounded-xl border border-gray-200 bg-white p-4 shadow-2xl sm:p-5"
                        >
                            <div class="grid items-center gap-4 border-b border-gray-100 pb-3 sm:grid-cols-2">
                                <div class="flex items-center gap-1">
                                    <button type="button" data-range-prev-year aria-label="{{ __('Profile::futapay.previous_year') }}" class="grid size-8 place-items-center rounded-full text-slate-500 hover:bg-orange-50 hover:text-[#ef5222] focus-visible:outline-2 focus-visible:outline-[#ef5222]">
                                        <x-heroicon-o-chevron-double-left class="size-4" />
                                    </button>
                                    <button type="button" data-range-prev-month aria-label="{{ __('Profile::app.previous_month') }}" class="grid size-8 place-items-center rounded-full text-slate-500 hover:bg-orange-50 hover:text-[#ef5222] focus-visible:outline-2 focus-visible:outline-[#ef5222]">
                                        <x-heroicon-o-chevron-left class="size-4" />
                                    </button>
                                    <span data-range-first-month class="flex-1 text-center text-base font-semibold text-gray-950"></span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <span data-range-second-month class="flex-1 text-center text-base font-semibold text-gray-950"></span>
                                    <button type="button" data-range-next-month aria-label="{{ __('Profile::app.next_month') }}" class="grid size-8 place-items-center rounded-full text-slate-500 hover:bg-orange-50 hover:text-[#ef5222] focus-visible:outline-2 focus-visible:outline-[#ef5222]">
                                        <x-heroicon-o-chevron-right class="size-4" />
                                    </button>
                                    <button type="button" data-range-next-year aria-label="{{ __('Profile::futapay.next_year') }}" class="grid size-8 place-items-center rounded-full text-slate-500 hover:bg-orange-50 hover:text-[#ef5222] focus-visible:outline-2 focus-visible:outline-[#ef5222]">
                                        <x-heroicon-o-chevron-double-right class="size-4" />
                                    </button>
                                </div>
                            </div>
                            <div class="mt-4 grid gap-5 sm:grid-cols-2 sm:gap-8">
                                @foreach(['first', 'second'] as $month)
                                    <div>
                                        <div data-range-weekdays="{{ $month }}" class="grid grid-cols-7 text-center text-xs font-semibold text-slate-500"></div>
                                        <div data-range-days="{{ $month }}" class="mt-2 grid grid-cols-7 gap-0.5"></div>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" data-range-clear class="mt-4 rounded-lg px-3 py-2 text-sm font-semibold text-[#ef5222] hover:bg-orange-50 focus-visible:outline-2 focus-visible:outline-[#ef5222]">
                                {{ __('Profile::futapay.clear_dates') }}
                            </button>
                        </div>
                    </div>
                    <div data-ticket-status-picker class="relative min-w-42">
                        <span id="futapay-status-label" class="mb-1.5 block text-sm font-semibold text-gray-900">{{ __('Profile::futapay.status') }}</span>
                        <input type="hidden" value="" data-ticket-status-value>
                        <button
                            type="button"
                            data-ticket-status-toggle
                            aria-labelledby="futapay-status-label futapay-status-display"
                            aria-haspopup="listbox"
                            aria-controls="futapay-status-options"
                            aria-expanded="false"
                            class="flex h-11 w-full items-center justify-between gap-2 rounded-lg border border-gray-200 bg-white px-3 text-left text-sm font-medium text-gray-950 hover:border-[#ef5222] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#ef5222]"
                        >
                            <span id="futapay-status-display" data-ticket-status-label class="truncate">{{ __('Profile::futapay.choose_status') }}</span>
                            <x-heroicon-o-chevron-down class="size-4 shrink-0 text-slate-500" />
                        </button>
                        <div
                            id="futapay-status-options"
                            data-ticket-status-options
                            role="listbox"
                            aria-label="{{ __('Profile::futapay.status') }}"
                            hidden
                            class="absolute left-0 top-full z-40 mt-2 max-h-64 w-full min-w-45 overflow-y-auto rounded-xl border border-gray-200 bg-white py-1 shadow-xl"
                        >
                            @foreach([
                                '' => 'choose_status',
                                'initialized' => 'initialized',
                                'pending' => 'pending',
                                'cancelled' => 'cancelled',
                                'approved' => 'approved',
                            ] as $value => $label)
                                <button
                                    type="button" role="option" tabindex="-1" data-ticket-status-option="{{ $value }}"
                                    aria-selected="{{ $value === '' ? 'true' : 'false' }}"
                                    class="block w-full px-3 py-2.5 text-left text-sm font-medium text-gray-900 hover:bg-orange-50 focus:bg-orange-50 focus:outline-none aria-selected:bg-orange-50 aria-selected:font-semibold aria-selected:text-[#ef5222]"
                                >{{ __('Profile::futapay.'.$label) }}</button>
                            @endforeach
                        </div>
                    </div>
                    <button
                        type="button"
                        data-notice-trigger
                        data-notice-title="{{ __('Profile::futapay.notice_title') }}"
                        data-notice-message="{{ __('Profile::futapay.notice_message') }}"
                        class="inline-flex h-11 min-w-25 items-center justify-center gap-2 rounded-full border
                            border-gray-200 bg-white px-5 text-sm font-semibold text-gray-900 shadow-sm transition
                            hover:border-[#ef5222] hover:text-[#ef5222] focus-visible:outline-2
                            focus-visible:outline-offset-2 focus-visible:outline-[#ef5222]"
                    >
                        <x-heroicon-o-funnel class="size-4" />
                        {{ __('Profile::futapay.search') }}
                    </button>
                </div>

                <div class="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-150 text-left text-sm">
                            <caption class="sr-only">{{ __('Profile::futapay.history') }}</caption>
                            <thead class="bg-gray-50 text-gray-800">
                                <tr>
                                    @foreach(['transaction_code', 'amount', 'content', 'time', 'status'] as $column)
                                        <th scope="col" class="whitespace-nowrap border-b border-gray-200 px-4 py-4 font-semibold">
                                            {{ __('Profile::futapay.'.$column) }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="px-5 py-14 text-center">
                                        <span class="mx-auto grid size-15 place-items-center rounded-full bg-gray-50 text-gray-300">
                                            <x-heroicon-o-inbox class="size-8" />
                                        </span>
                                        <p class="mt-3 text-sm font-medium text-slate-500">{{ __('Profile::futapay.empty') }}</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>

        @include('core::partials.home.footer')
    </div>
    @vite('packages/FuteBus/Profile/src/resources/js/app.js')
@endsection
