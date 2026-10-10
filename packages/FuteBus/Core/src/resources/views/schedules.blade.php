@extends('core::layouts.home')

@section('title', __('core::schedules.meta.title'))
@section('meta_description', __('core::schedules.meta.description'))

@section('content')
    <div class="home-page min-h-screen">
        @include('core::partials.home.navbar')

        <main
            class="mx-auto min-h-162.5 w-full max-w-275 px-4 py-8 sm:px-6 lg:px-0"
            x-data="{
                from: '',
                to: '',
                fromOpen: false,
                toOpen: false,
                groups: @js($scheduleGroups),
                normalize(value) {
                    return String(value ?? '')
                        .normalize('NFD')
                        .replace(/[\u0300-\u036f]/g, '')
                        .replace(/đ/g, 'd')
                        .replace(/Đ/g, 'D')
                        .toLowerCase();
                },
                filtered(group) {
                    const fromKeyword = this.normalize(this.from.trim());
                    const toKeyword = this.normalize(this.to.trim());

                    return group.filter((route) => {
                        return this.normalize(route.from).includes(fromKeyword)
                            && this.normalize(route.to).includes(toKeyword);
                    });
                },
                hasResults() {
                    return this.groups.some((group) => this.filtered(group).length > 0);
                },
                suggestions(type) {
                    const field = type === 'from' ? 'from' : 'to';
                    const oppositeField = type === 'from' ? 'to' : 'from';
                    const keyword = this.normalize(this[field].trim());
                    const oppositeKeyword = this.normalize(this[oppositeField].trim());
                    const routes = this.groups.flat().filter((route) => {
                        return oppositeKeyword === ''
                            || this.normalize(route[oppositeField]).includes(oppositeKeyword);
                    });
                    const values = routes
                        .map((route) => route[field])
                        .filter((value) => this.normalize(value).includes(keyword));

                    return [...new Map(values.map((value) => [this.normalize(value), value])).values()]
                        .slice(0, 12);
                },
                choosePoint(type, value) {
                    this[type] = value;
                    this[`${type}Open`] = false;
                },
                swap() {
                    [this.from, this.to] = [this.to, this.from];
                    this.fromOpen = false;
                    this.toOpen = false;
                },
            }"
        >
            <section aria-label="{{ __('core::app.home.navbar.schedules') }}">
                <div class="relative grid gap-3 sm:grid-cols-2 sm:gap-6">
                    <div class="relative" @click.outside="fromOpen = false">
                        <span class="sr-only">{{ __('core::schedules.from_placeholder') }}</span>
                        <x-heroicon-o-magnifying-glass
                            class="pointer-events-none absolute left-5 top-1/2 size-5 -translate-y-1/2 text-gray-400"
                        />
                        <input
                            type="search"
                            x-model.debounce.150ms="from"
                            @focus="fromOpen = true"
                            @input="fromOpen = true"
                            @keydown.escape="fromOpen = false"
                            placeholder="{{ __('core::schedules.from_placeholder') }}"
                            class="h-11 w-full rounded-full border border-gray-300 bg-white pl-13 pr-5 text-base font-semibold text-gray-900 outline-none transition placeholder:font-medium placeholder:text-gray-400 focus:border-futa-orange focus:ring-3 focus:ring-futa-orange/10"
                        >
                        <div
                            x-cloak
                            x-show="fromOpen && suggestions('from').length > 0"
                            x-transition.opacity.duration.150ms
                            class="scrollbar-hidden absolute inset-x-0 top-full z-30 mt-2 max-h-72 overflow-y-auto rounded-2xl border border-gray-200 bg-white py-2 shadow-[0_12px_30px_rgba(15,23,42,.16)]"
                        >
                            <template x-for="point in suggestions('from')" :key="point">
                                <button
                                    type="button"
                                    @click="choosePoint('from', point)"
                                    class="flex w-full items-center gap-3 px-5 py-3 text-left text-sm font-semibold text-gray-800 transition hover:bg-futa-orange-soft hover:text-futa-orange"
                                >
                                    <x-heroicon-o-map-pin class="size-4.5 shrink-0 text-futa-green" />
                                    <span x-text="point"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="swap"
                        title="{{ __('core::schedules.swap') }}"
                        aria-label="{{ __('core::schedules.swap') }}"
                        class="group absolute left-1/2 top-1/2 z-10 grid size-9 -translate-x-1/2 -translate-y-1/2 place-items-center rounded-full border border-gray-200 bg-white text-futa-orange shadow-sm transition hover:border-futa-orange hover:shadow-md max-sm:hidden"
                    >
                        <x-heroicon-o-arrows-right-left
                            class="size-4 transition-transform duration-300 ease-out group-hover:rotate-180"
                        />
                    </button>

                    <div class="relative" @click.outside="toOpen = false">
                        <span class="sr-only">{{ __('core::schedules.to_placeholder') }}</span>
                        <x-heroicon-o-magnifying-glass
                            class="pointer-events-none absolute left-5 top-1/2 size-5 -translate-y-1/2 text-gray-400"
                        />
                        <input
                            type="search"
                            x-model.debounce.150ms="to"
                            @focus="toOpen = true"
                            @input="toOpen = true"
                            @keydown.escape="toOpen = false"
                            placeholder="{{ __('core::schedules.to_placeholder') }}"
                            class="h-11 w-full rounded-full border border-gray-300 bg-white pl-13 pr-5 text-base font-semibold text-gray-900 outline-none transition placeholder:font-medium placeholder:text-gray-400 focus:border-futa-orange focus:ring-3 focus:ring-futa-orange/10"
                        >
                        <div
                            x-cloak
                            x-show="toOpen && suggestions('to').length > 0"
                            x-transition.opacity.duration.150ms
                            class="scrollbar-hidden absolute inset-x-0 top-full z-30 mt-2 max-h-72 overflow-y-auto rounded-2xl border border-gray-200 bg-white py-2 shadow-[0_12px_30px_rgba(15,23,42,.16)]"
                        >
                            <template x-for="point in suggestions('to')" :key="point">
                                <button
                                    type="button"
                                    @click="choosePoint('to', point)"
                                    class="flex w-full items-center gap-3 px-5 py-3 text-left text-sm font-semibold text-gray-800 transition hover:bg-futa-orange-soft hover:text-futa-orange"
                                >
                                    <x-heroicon-o-map-pin class="size-4.5 shrink-0 text-futa-green" />
                                    <span x-text="point"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="mt-6 hidden grid-cols-[minmax(0,1fr)_130px_130px_175px] items-center gap-4 rounded-2xl border border-gray-200 bg-gray-50 px-5 py-4 text-[15px] font-extrabold text-gray-950 md:grid">
                    <span>{{ __('core::schedules.route') }}</span>
                    <span>{{ __('core::schedules.vehicle_type') }}</span>
                    <span>{{ __('core::schedules.distance') }}</span>
                    <span>{{ __('core::schedules.duration') }}</span>
                </div>

                <div class="mt-4 space-y-4">
                    <template x-for="(group, groupIndex) in groups" :key="groupIndex">
                        <div
                            x-show="filtered(group).length > 0"
                            x-transition.opacity.duration.150ms
                            class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-[0_4px_16px_rgba(15,23,42,.035)]"
                        >
                            <template x-for="(route, routeIndex) in filtered(group)" :key="`${groupIndex}-${routeIndex}`">
                                <article
                                    class="grid gap-3 border-b border-gray-100 px-4 py-4 transition-colors last:border-b-0 hover:bg-futa-orange-soft/60 md:grid-cols-[minmax(0,1fr)_130px_130px_175px] md:items-center md:gap-4 md:px-5 md:py-3"
                                >
                                    <h2 class="text-base font-bold text-futa-orange">
                                        <span x-text="route.from"></span>
                                        <span aria-hidden="true"> – </span>
                                        <span x-text="route.to"></span>
                                    </h2>

                                    <dl class="grid grid-cols-3 gap-3 md:contents">
                                        <div class="min-w-0 md:block">
                                            <dt class="text-xs font-bold text-gray-500 md:hidden">
                                                {{ __('core::schedules.vehicle_type') }}
                                            </dt>
                                            <dd
                                                class="mt-1 truncate text-sm font-semibold text-gray-950 md:mt-0 md:text-base"
                                                x-text="route.vehicle || @js(__('core::schedules.updating'))"
                                            ></dd>
                                        </div>
                                        <div class="min-w-0 md:block">
                                            <dt class="text-xs font-bold text-gray-500 md:hidden">
                                                {{ __('core::schedules.distance') }}
                                            </dt>
                                            <dd class="mt-1 text-sm font-semibold text-gray-950 md:mt-0 md:text-base">
                                                <span x-text="route.distance"></span><span>km</span>
                                            </dd>
                                        </div>
                                        <div class="min-w-0 md:block">
                                            <dt class="text-xs font-bold text-gray-500 md:hidden">
                                                {{ __('core::schedules.duration') }}
                                            </dt>
                                            <dd class="mt-1 text-sm font-semibold text-gray-950 md:mt-0 md:text-base">
                                                <span x-text="route.hours"></span>
                                                <span>{{ app()->getLocale() === 'vi' ? ' giờ' : ' hours' }}</span>
                                            </dd>
                                        </div>
                                    </dl>
                                </article>
                            </template>
                        </div>
                    </template>

                    <p
                        x-cloak
                        x-show="!hasResults()"
                        x-transition.opacity.duration.150ms
                        class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-5 py-12 text-center text-base font-semibold text-gray-500"
                    >
                        {{ __('core::schedules.no_results') }}
                    </p>
                </div>
            </section>
        </main>

        @include('core::partials.home.footer')
    </div>
@endsection
