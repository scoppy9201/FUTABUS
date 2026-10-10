@extends('core::layouts.home')

@section('title', __('core::invoice.meta.title'))
@section('meta_description', __('core::invoice.meta.description'))

@section('content')
    <div class="home-page min-h-screen bg-[#fffdfb]">
        @include('core::partials.home.navbar')

        <main
            class="relative overflow-hidden px-4 py-10 sm:px-6 sm:py-14"
            x-data="{
                tab: 'lookup',
                captcha: '7K4M9',
                fileName: '',
                refreshCaptcha() {
                    this.captcha = Math.random().toString(36).slice(2, 7).toUpperCase()
                }
            }"
        >
            <div class="pointer-events-none absolute -top-32 left-1/2 size-96 -translate-x-1/2 rounded-full bg-futa-orange-soft/60 blur-3xl"></div>

            <div class="relative mx-auto max-w-225">
                <header class="text-center">
                    <span class="inline-flex items-center gap-2 rounded-full bg-futa-orange-soft px-4 py-2 text-xs font-extrabold uppercase tracking-[0.12em] text-futa-orange">
                        <x-heroicon-o-document-text class="size-4" />
                        {{ __('core::invoice.eyebrow') }}
                    </span>
                    <h1 class="mt-4 text-3xl font-black uppercase text-futa-green sm:text-4xl">
                        {{ __('core::invoice.title') }}
                    </h1>
                    <p class="mx-auto mt-3 max-w-160 text-sm font-semibold leading-6 text-gray-600 sm:text-base">
                        {{ __('core::invoice.description') }}
                    </p>
                </header>

                <section class="mt-8 overflow-hidden rounded-3xl border border-futa-orange-soft bg-white shadow-[0_18px_55px_rgba(125,63,25,0.12)] sm:mt-10">
                    <div class="grid border-b border-gray-100 bg-[#fff8f4] p-2 sm:grid-cols-2">
                        <button
                            type="button"
                            class="flex min-h-12 items-center justify-center gap-2 rounded-2xl px-4 text-sm font-extrabold transition-all duration-200"
                            :class="tab === 'lookup' ? 'bg-futa-orange text-white shadow-md' : 'text-gray-600 hover:bg-white hover:text-futa-orange'"
                            @click="tab = 'lookup'"
                        >
                            <x-heroicon-o-magnifying-glass class="size-5" />
                            {{ __('core::invoice.lookup_tab') }}
                        </button>
                        <button
                            type="button"
                            class="flex min-h-12 items-center justify-center gap-2 rounded-2xl px-4 text-sm font-extrabold transition-all duration-200"
                            :class="tab === 'verify' ? 'bg-futa-orange text-white shadow-md' : 'text-gray-600 hover:bg-white hover:text-futa-orange'"
                            @click="tab = 'verify'"
                        >
                            <x-heroicon-o-shield-check class="size-5" />
                            {{ __('core::invoice.verify_tab') }}
                        </button>
                    </div>

                    <div class="p-5 sm:p-8 lg:p-10">
                        <div
                            x-show="tab === 'lookup'"
                            x-transition:enter="transition duration-250 ease-out"
                            x-transition:enter-start="translate-x-3 opacity-0"
                            x-transition:enter-end="translate-x-0 opacity-100"
                        >
                            <div class="flex gap-4">
                                <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-futa-orange-soft text-futa-orange">
                                    <x-heroicon-o-receipt-percent class="size-6" />
                                </span>
                                <div>
                                    <h2 class="text-xl font-black text-gray-950">{{ __('core::invoice.lookup_title') }}</h2>
                                    <p class="mt-1 text-sm font-medium leading-6 text-gray-500">{{ __('core::invoice.lookup_description') }}</p>
                                </div>
                            </div>

                            <form class="mt-7 space-y-5" @submit.prevent>
                                <div class="grid gap-5 sm:grid-cols-2">
                                    <label class="block">
                                        <span class="mb-2 block text-sm font-extrabold text-gray-800">{{ __('core::invoice.tax_code') }}</span>
                                        <span class="relative block">
                                            <x-heroicon-o-identification class="absolute left-4 top-1/2 size-5 -translate-y-1/2 text-gray-400" />
                                            <input type="text" inputmode="numeric" autocomplete="off" class="h-12 w-full rounded-xl border border-gray-300 bg-white pr-4 pl-12 text-sm font-semibold outline-none transition placeholder:text-gray-400 hover:border-gray-400 focus:border-futa-orange focus:ring-4 focus:ring-futa-orange/20" placeholder="0101234567" required>
                                        </span>
                                    </label>
                                    <label class="block">
                                        <span class="mb-2 block text-sm font-extrabold text-gray-800">{{ __('core::invoice.invoice_code') }}</span>
                                        <span class="relative block">
                                            <x-heroicon-o-qr-code class="absolute left-4 top-1/2 size-5 -translate-y-1/2 text-gray-400" />
                                            <input type="text" autocomplete="off" class="h-12 w-full rounded-xl border border-gray-300 bg-white pr-4 pl-12 text-sm font-semibold uppercase outline-none transition placeholder:normal-case placeholder:text-gray-400 hover:border-gray-400 focus:border-futa-orange focus:ring-4 focus:ring-futa-orange/20" placeholder="FTB-XXXXXXXXX" required>
                                        </span>
                                    </label>
                                </div>

                                @include('core::partials.invoice-captcha')

                                <div class="pt-1 text-center">
                                    <button type="submit" class="inline-flex min-h-11 min-w-52 items-center justify-center gap-2 rounded-xl bg-futa-orange px-8 text-sm font-extrabold text-white shadow-[0_8px_20px_rgba(239,82,34,0.25)] transition hover:-translate-y-0.5 hover:bg-futa-orange-dark focus:outline-none focus:ring-4 focus:ring-futa-orange/25">
                                        <x-heroicon-o-magnifying-glass class="size-5" />
                                        {{ __('core::invoice.lookup_button') }}
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div
                            x-cloak
                            x-show="tab === 'verify'"
                            x-transition:enter="transition duration-250 ease-out"
                            x-transition:enter-start="translate-x-3 opacity-0"
                            x-transition:enter-end="translate-x-0 opacity-100"
                        >
                            <div class="flex gap-4">
                                <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-futa-orange-soft text-futa-orange">
                                    <x-heroicon-o-shield-check class="size-6" />
                                </span>
                                <div>
                                    <h2 class="text-xl font-black text-gray-950">{{ __('core::invoice.verify_title') }}</h2>
                                    <p class="mt-1 text-sm font-medium leading-6 text-gray-500">{{ __('core::invoice.verify_description') }}</p>
                                </div>
                            </div>

                            <form class="mt-7 space-y-5" @submit.prevent>
                                <div>
                                    <span class="mb-2 block text-sm font-extrabold text-gray-800">{{ __('core::invoice.xml_file') }}</span>
                                    <label class="group flex min-h-28 cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-300 bg-gray-50 px-5 text-center transition hover:border-futa-orange hover:bg-futa-orange-soft/50">
                                        <input type="file" accept=".xml,text/xml,application/xml" class="sr-only" required @change="fileName = $event.target.files[0]?.name || ''">
                                        <x-heroicon-o-arrow-up-tray class="size-7 text-futa-orange transition-transform group-hover:-translate-y-0.5" />
                                        <span class="mt-2 text-sm font-extrabold text-gray-800" x-text="fileName || @js(__('core::invoice.choose_file'))"></span>
                                        <span class="mt-1 text-xs font-semibold text-gray-400">{{ __('core::invoice.file_hint') }}</span>
                                    </label>
                                </div>

                                @include('core::partials.invoice-captcha')

                                <div class="pt-1 text-center">
                                    <button type="submit" class="inline-flex min-h-11 min-w-52 items-center justify-center gap-2 rounded-xl bg-futa-orange px-8 text-sm font-extrabold text-white shadow-[0_8px_20px_rgba(239,82,34,0.25)] transition hover:-translate-y-0.5 hover:bg-futa-orange-dark focus:outline-none focus:ring-4 focus:ring-futa-orange/25">
                                        <x-heroicon-o-shield-check class="size-5" />
                                        {{ __('core::invoice.verify_button') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>

                <aside class="mt-5 grid gap-3 rounded-2xl border border-emerald-100 bg-emerald-50/70 p-4 sm:grid-cols-[auto_1fr_auto] sm:items-center sm:px-5">
                    <span class="grid size-9 place-items-center rounded-full bg-white text-futa-green shadow-sm">
                        <x-heroicon-o-lock-closed class="size-5" />
                    </span>
                    <div>
                        <h2 class="text-sm font-extrabold text-futa-green">{{ __('core::invoice.security_title') }}</h2>
                        <p class="mt-0.5 text-xs font-semibold leading-5 text-gray-600">{{ __('core::invoice.security_text') }}</p>
                    </div>
                    <a href="tel:19006067" class="text-sm font-extrabold whitespace-nowrap text-futa-orange hover:underline">
                        {{ __('core::invoice.support') }} 1900 6067
                    </a>
                </aside>
            </div>
        </main>

        @include('core::partials.home.footer')
    </div>
@endsection
