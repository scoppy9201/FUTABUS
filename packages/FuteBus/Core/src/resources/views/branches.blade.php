@extends('core::layouts.home')

@section('title', __('core::branches.meta.title'))
@section('meta_description', __('core::branches.meta.description'))

@php
    $directory = $regions->map(fn ($region) => [
        'id' => $region->id,
        'name' => $region->localizedName(),
        'offices' => $region->offices->map(function ($office) {
            $name = $office->localized('name');
            $address = $office->localized('address');
            $destination = $office->destination();

            return [
                'id' => $office->id,
                'name' => $name,
                'address' => $address,
                'phone' => $office->phone,
                'phone_href' => preg_replace('/[^0-9+]/', '', $office->phone ?? ''),
                'search_text' => mb_strtolower("{$name} {$address} {$office->phone}"),
                'map_url' => 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($destination),
                'directions_url' => 'https://www.google.com/maps/dir/?api=1&destination='.rawurlencode($destination),
            ];
        })->values(),
    ])->values();
@endphp

@section('content')
    <div class="home-page min-h-screen">
        @include('core::partials.home.navbar')

        <main
            class="mx-auto min-h-155 w-full max-w-285 px-4 py-10 sm:px-6 lg:px-0"
            x-data="{
                query: '',
                regions: @js($directory),
                normalize(value) {
                    return String(value ?? '')
                        .normalize('NFD')
                        .replace(/[\u0300-\u036f]/g, '')
                        .replace(/đ/g, 'd')
                        .replace(/Đ/g, 'D')
                        .toLowerCase();
                },
                filtered(offices) {
                    const keyword = this.normalize(this.query.trim());
                    return keyword === ''
                        ? offices
                        : offices.filter((office) => this.normalize(office.search_text).includes(keyword));
                },
                hasResults() {
                    return this.regions.some((region) => this.filtered(region.offices).length > 0);
                },
            }"
        >
            <h1 class="text-[25px] font-extrabold uppercase leading-tight text-[#ef5222]">
                {{ __('core::branches.heading') }}
            </h1>

            <label class="relative mt-4 block">
                <span class="sr-only">{{ __('core::branches.search_placeholder') }}</span>
                <x-heroicon-o-magnifying-glass
                    class="pointer-events-none absolute left-4 top-1/2 size-5 -translate-y-1/2 text-gray-400"
                />
                <input
                    type="search"
                    x-model.debounce.180ms="query"
                    placeholder="{{ __('core::branches.search_placeholder') }}"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-white pl-12 pr-4 text-base font-medium text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-[#ef5222] focus:ring-2 focus:ring-[#ef5222]/15"
                >
            </label>

            <div class="mt-8 space-y-9">
                <template x-for="region in regions" :key="region.id">
                    <section x-show="filtered(region.offices).length > 0" x-transition.opacity.duration.180ms>
                        <h2 class="text-xl font-extrabold text-gray-950" x-text="region.name"></h2>

                        <div class="mt-5 grid gap-x-8 gap-y-7 md:grid-cols-2 lg:grid-cols-3">
                            <template x-for="office in filtered(region.offices)" :key="office.id">
                                <article class="grid min-w-0 grid-rows-[40px_minmax(52px,auto)_36px_36px]">
                                    <h3
                                        class="line-clamp-2 self-start text-base font-bold uppercase leading-5 text-[#ef5222]"
                                        x-text="office.name"
                                    ></h3>

                                    <a
                                        :href="office.map_url"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="group flex items-start gap-2 py-1.5 text-[15px] font-medium leading-5 text-gray-950"
                                    >
                                        <x-heroicon-o-map-pin class="mt-0.5 size-4.5 shrink-0 text-[#00613d]" />
                                        <span
                                            class="transition-colors duration-150 group-hover:text-[#ef5222]"
                                            x-text="office.address"
                                        ></span>
                                    </a>

                                    <a
                                        :href="office.directions_url"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="group flex items-center gap-2 py-1.5 text-[15px] font-medium text-gray-950"
                                    >
                                        <x-heroicon-o-arrow-turn-up-right class="size-4.5 shrink-0 text-[#00613d]" />
                                        <span class="transition-colors duration-150 group-hover:text-[#ef5222]">
                                            {{ __('core::branches.directions') }}
                                        </span>
                                    </a>

                                    <a
                                        :href="`tel:${office.phone_href}`"
                                        class="group flex items-center gap-2 py-1.5 text-[15px] font-medium text-gray-950"
                                    >
                                        <x-heroicon-o-phone class="size-4.5 shrink-0 text-[#00613d]" />
                                        <span
                                            class="transition-colors duration-150 group-hover:text-[#ef5222]"
                                            x-text="office.phone"
                                        ></span>
                                    </a>
                                </article>
                            </template>
                        </div>
                    </section>
                </template>

                <p
                    x-cloak
                    x-show="!hasResults()"
                    x-transition.opacity.duration.180ms
                    class="rounded-lg bg-gray-50 px-5 py-10 text-center text-base font-semibold text-gray-500"
                >
                    {{ __('core::branches.no_results') }}
                </p>
            </div>
        </main>

        @include('core::partials.home.footer')
    </div>
@endsection
