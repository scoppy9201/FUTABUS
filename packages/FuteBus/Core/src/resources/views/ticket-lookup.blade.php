@extends('core::layouts.home')

@section('title', __('core::ticket-lookup.meta.title'))
@section('meta_description', __('core::ticket-lookup.meta.description'))

@section('content')
    <div class="home-page min-h-screen">
        @include('core::partials.home.navbar')

        <main class="mx-auto min-h-181 w-full max-w-285 px-4 py-9 sm:px-6 lg:px-0">
            <section class="mx-auto w-full max-w-150">
                <h1 class="text-center text-[22px] font-extrabold uppercase leading-tight text-[#00613d]">
                    {{ __('core::ticket-lookup.heading') }}
                </h1>

                <form class="mt-6 space-y-6" @submit.prevent>
                    <div class="relative">
                        <input
                            id="lookup-phone"
                            type="tel"
                            name="phone"
                            inputmode="tel"
                            autocomplete="tel"
                            placeholder="{{ __('core::ticket-lookup.phone_placeholder') }}"
                            class="peer h-10 w-full rounded-lg border border-gray-300 bg-white px-3 py-0 text-sm
                                font-semibold leading-normal text-gray-900 outline-none transition duration-200
                                placeholder:font-medium placeholder:text-gray-400 focus:border-[#ef5222]
                                focus:placeholder:text-transparent focus:ring-3 focus:ring-[#ef5222]/10"
                        >
                        <label
                            for="lookup-phone"
                            class="pointer-events-none absolute left-2.5 top-0 -translate-y-1/2 bg-white px-1 text-xs
                                font-semibold text-[#ef5222] opacity-100 transition-all duration-200 ease-out
                                peer-placeholder-shown:opacity-0 peer-focus:top-0 peer-focus:-translate-y-1/2
                                peer-focus:text-xs peer-focus:font-semibold peer-focus:text-[#ef5222]
                                peer-focus:opacity-100"
                        >
                            {{ __('core::ticket-lookup.phone_label') }}
                        </label>
                    </div>

                    <div class="relative">
                        <input
                            id="lookup-ticket-code"
                            type="text"
                            name="ticket_code"
                            autocomplete="off"
                            placeholder="{{ __('core::ticket-lookup.code_placeholder') }}"
                            class="peer h-10 w-full rounded-lg border border-gray-300 bg-white px-3 py-0 text-sm
                                font-semibold uppercase leading-normal text-gray-900 outline-none transition duration-200
                                placeholder:normal-case placeholder:font-medium placeholder:text-gray-400
                                focus:border-[#ef5222] focus:placeholder:text-transparent
                                focus:ring-3 focus:ring-[#ef5222]/10"
                        >
                        <label
                            for="lookup-ticket-code"
                            class="pointer-events-none absolute left-2.5 top-0 -translate-y-1/2 bg-white px-1 text-xs
                                font-semibold text-[#ef5222] opacity-100 transition-all duration-200 ease-out
                                peer-placeholder-shown:opacity-0 peer-focus:top-0 peer-focus:-translate-y-1/2
                                peer-focus:text-xs peer-focus:font-semibold peer-focus:text-[#ef5222]
                                peer-focus:opacity-100"
                        >
                            {{ __('core::ticket-lookup.code_label') }}
                        </label>
                    </div>

                    <button
                        type="submit"
                        class="mx-auto block h-9 w-56 rounded-full bg-[#fff0eb] text-sm font-extrabold text-[#ef5222] transition hover:bg-[#ef5222] hover:text-white focus:outline-none focus:ring-3 focus:ring-[#ef5222]/20"
                    >
                        {{ __('core::ticket-lookup.submit') }}
                    </button>
                </form>
            </section>
        </main>

        @include('core::partials.home.footer')
    </div>
@endsection
