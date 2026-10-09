@extends('core::layouts.home')

@section('title', __('Payment::payment.sepay_success_title'))

@section('content')
    <div class="payment-page">
        <div class="booking-page__banner">
            @include('core::partials.home.navbar')
            <div class="booking-page__hero-inner">
                <div class="booking-page__heading">
                    <h1 class="booking-page__route">{{ __('Payment::payment.sepay_success_title') }}</h1>
                </div>
            </div>
        </div>

        <main class="payment-page__result">
            <img src="{{ asset('icons/notifications/success.svg') }}" alt="" aria-hidden="true">
            <h2>{{ __('Payment::payment.sepay_success_title') }}</h2>
            <p>{{ __('Payment::payment.sepay_success_message') }}</p>
            <dl>
                <div><dt>{{ __('Payment::payment.sepay_booking_code') }}</dt><dd>{{ $bookingCode }}</dd></div>
                <div><dt>{{ __('core::booking.route') }}</dt>
                    <dd>{{ $preview['trip']['origin'] }} – {{ $preview['trip']['destination'] }}</dd></div>
                <div><dt>{{ __('core::booking.seats') }}</dt><dd>{{ implode(', ', $preview['seats']) }}</dd></div>
            </dl>
            <a href="{{ route('ticket-lookup') }}">{{ __('Payment::payment.sepay_look_up_ticket') }}</a>
        </main>
        @include('core::partials.home.footer')
    </div>
@endsection