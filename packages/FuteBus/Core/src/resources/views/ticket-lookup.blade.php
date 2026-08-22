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
                    <div>
                        <label for="lookup-phone" class="sr-only">
                            {{ __('core::ticket-lookup.phone_label') }}
                        </label>
                        <input
                            id="lookup-phone"
                            type="tel"
                            name="phone"
                            inputmode="tel"
                            autocomplete="tel"
                            placeholder="{{ __('core::ticket-lookup.phone_placeholder') }}"
                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm font-semibold text-gray-900 outline-none transition placeholder:font-medium placeholder:text-gray-400 focus:border-[#ef5222] focus:ring-3 focus:ring-[#ef5222]/10"
                        >
                    </div>

                    <div>
                        <label for="lookup-ticket-code" class="sr-only">
                            {{ __('core::ticket-lookup.code_label') }}
                        </label>
                        <input
                            id="lookup-ticket-code"
                            type="text"
                            name="ticket_code"
                            autocomplete="off"
                            placeholder="{{ __('core::ticket-lookup.code_placeholder') }}"
                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm font-semibold uppercase text-gray-900 outline-none transition placeholder:normal-case placeholder:font-medium placeholder:text-gray-400 focus:border-[#ef5222] focus:ring-3 focus:ring-[#ef5222]/10"
                        >
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
