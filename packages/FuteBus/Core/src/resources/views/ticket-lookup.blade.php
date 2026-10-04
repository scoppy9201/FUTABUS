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

                <form action="{{ route('ticket-lookup.search') }}" method="post" class="mt-6 space-y-6">
                    @csrf
                    <div class="relative">
                        <input
                            id="lookup-phone"
                            type="tel"
                            name="phone"
                            inputmode="tel"
                            autocomplete="tel"
                            value="{{ $input['phone'] ?? old('phone') }}"
                            required
                            placeholder="{{ __('core::ticket-lookup.phone_placeholder') }}"
                            @class(['peer h-10 w-full rounded-lg border bg-white px-3 py-0 text-sm
                                font-semibold leading-normal text-gray-900 outline-none transition duration-200
                                placeholder:font-medium placeholder:text-gray-400 focus:border-[#ef5222]
                                focus:placeholder:text-transparent focus:ring-3 focus:ring-[#ef5222]/10',
                                'border-red-500' => $errors->has('phone'),
                                'border-gray-300' => ! $errors->has('phone')])
                            aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}"
                            aria-describedby="{{ $errors->has('phone') ? 'lookup-phone-error' : '' }}"
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
                        @error('phone')
                            <p id="lookup-phone-error" class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="relative">
                        <input
                            id="lookup-ticket-code"
                            type="text"
                            name="ticket_code"
                            autocomplete="off"
                            value="{{ $input['ticket_code'] ?? old('ticket_code') }}"
                            required
                            placeholder="{{ __('core::ticket-lookup.code_placeholder') }}"
                            @class(['peer h-10 w-full rounded-lg border bg-white px-3 py-0 text-sm
                                font-semibold uppercase leading-normal text-gray-900 outline-none transition duration-200
                                placeholder:normal-case placeholder:font-medium placeholder:text-gray-400
                                focus:border-[#ef5222] focus:placeholder:text-transparent
                                focus:ring-3 focus:ring-[#ef5222]/10',
                                'border-red-500' => $errors->has('ticket_code'),
                                'border-gray-300' => ! $errors->has('ticket_code')])
                            aria-invalid="{{ $errors->has('ticket_code') ? 'true' : 'false' }}"
                            aria-describedby="{{ $errors->has('ticket_code') ? 'lookup-code-error' : '' }}"
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
                        @error('ticket_code')
                            <p id="lookup-code-error" class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="mx-auto block h-10 w-56 rounded-full bg-[#ef5222] text-sm font-extrabold text-white shadow-sm transition hover:bg-[#d94418] focus:outline-none focus:ring-3 focus:ring-[#ef5222]/20"
                    >
                        {{ __('core::ticket-lookup.submit') }}
                    </button>
                </form>
            </section>

            @if($notFound ?? false)
                <div
                    hidden
                    data-notice-on-load
                    data-notice-tone="warning"
                    data-notice-title="{{ __('core::ticket-lookup.not_found_title') }}"
                    data-notice-message="{{ __('core::ticket-lookup.not_found') }}"
                    data-notice-label="{{ __('core::ticket-lookup.ok') }}"
                ></div>
                <noscript>
                    <p role="status" class="mx-auto mt-8 max-w-150 rounded-xl border border-orange-200 bg-orange-50 px-5 py-4 text-center text-sm font-medium text-gray-800">
                        {{ __('core::ticket-lookup.not_found') }}
                    </p>
                </noscript>
            @endif

            @if($result ?? null)
                @php($booking = $result['booking'])
                <section aria-labelledby="lookup-result-title" class="mx-auto mt-10 max-w-225 overflow-hidden rounded-2xl border border-orange-100 bg-white shadow-[0_8px_28px_rgba(83,50,29,.08)]">
                    <div class="border-b border-orange-100 bg-[#fff5ef] px-6 py-5 sm:px-8">
                        <p class="text-xs font-bold uppercase tracking-wider text-[#00613d]">{{ __('core::ticket-lookup.result_eyebrow') }}</p>
                        <h2 id="lookup-result-title" class="mt-1 text-2xl font-bold text-gray-950">{{ $booking->origin_city }} → {{ $booking->destination_city }}</h2>
                        <p class="mt-2 text-sm font-medium text-gray-600">{{ __('core::ticket-lookup.booking_code') }}: <span class="font-bold text-[#ef5222]">{{ $booking->booking_code }}</span></p>
                    </div>

                    <dl class="grid gap-x-8 gap-y-5 px-6 py-6 sm:grid-cols-2 sm:px-8">
                        <div><dt class="text-sm text-gray-500">{{ __('core::ticket-lookup.departure') }}</dt><dd class="mt-1 font-semibold text-gray-950">{{ \Illuminate\Support\Carbon::parse($booking->departure_time)->format('d/m/Y H:i') }}</dd></div>
                        <div><dt class="text-sm text-gray-500">{{ __('core::ticket-lookup.arrival') }}</dt><dd class="mt-1 font-semibold text-gray-950">{{ \Illuminate\Support\Carbon::parse($booking->arrival_time)->format('d/m/Y H:i') }}</dd></div>
                        <div><dt class="text-sm text-gray-500">{{ __('core::ticket-lookup.pickup') }}</dt><dd class="mt-1 font-semibold text-gray-950">{{ $booking->origin_station ?: $booking->origin_city }}</dd></div>
                        <div><dt class="text-sm text-gray-500">{{ __('core::ticket-lookup.dropoff') }}</dt><dd class="mt-1 font-semibold text-gray-950">{{ $booking->destination_station ?: $booking->destination_city }}</dd></div>
                        <div><dt class="text-sm text-gray-500">{{ __('core::ticket-lookup.vehicle') }}</dt><dd class="mt-1 font-semibold text-gray-950">{{ __('core::ticket-lookup.bus_type.'.$booking->bus_type) }}</dd></div>
                        <div><dt class="text-sm text-gray-500">{{ __('core::ticket-lookup.seat_count') }}</dt><dd class="mt-1 font-semibold text-gray-950">{{ $booking->seat_count }}</dd></div>
                        <div><dt class="text-sm text-gray-500">{{ __('core::ticket-lookup.booking_status_label') }}</dt><dd class="mt-1 font-semibold text-gray-950">{{ __('core::ticket-lookup.booking_status.'.$booking->status) }}</dd></div>
                        <div><dt class="text-sm text-gray-500">{{ __('core::ticket-lookup.payment_status_label') }}</dt><dd class="mt-1 font-semibold text-gray-950">{{ __('core::ticket-lookup.payment_status.'.$result['paymentStatus']) }}</dd></div>
                        <div><dt class="text-sm text-gray-500">{{ __('core::ticket-lookup.total_amount') }}</dt><dd class="mt-1 font-bold text-[#ef5222]">{{ number_format((float) $booking->total_amount, 0, ',', '.') }} đ</dd></div>
                    </dl>

                    @if($result['tickets']->isNotEmpty())
                        <div class="border-t border-gray-100 px-6 py-5 sm:px-8">
                            <h3 class="font-bold text-gray-950">{{ __('core::ticket-lookup.ticket_details') }}</h3>
                            <ul class="mt-3 grid gap-3 sm:grid-cols-2">
                                @foreach($result['tickets'] as $ticket)
                                    <li class="rounded-xl border border-gray-200 px-4 py-3">
                                        <p class="font-bold text-[#ef5222]">{{ $ticket->ticket_code }}</p>
                                        <p class="mt-1 text-sm text-gray-600">{{ __('core::ticket-lookup.seat') }}: {{ $ticket->seat_code ?: __('core::ticket-lookup.unassigned') }}</p>
                                        <p class="text-sm text-gray-600">{{ __('core::ticket-lookup.ticket_status.'.$ticket->status) }}</p>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </section>
            @endif
        </main>

        @include('core::partials.home.footer')
    </div>
@endsection
