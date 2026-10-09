@extends('core::layouts.home')

@section('title', __('Payment::payment.sepay_review_title'))

@section('content')
    <div class="payment-page">
        <div class="booking-page__banner">
            @include('core::partials.home.navbar')
            <div class="booking-page__hero-inner">
                <div class="booking-page__heading">
                    <h1 class="booking-page__route">{{ __('Payment::payment.sepay_review_title') }}</h1>
                </div>
            </div>
        </div>

        <main class="payment-page__result">
            <img src="{{ asset('icons/notifications/warning.svg') }}" alt="" aria-hidden="true">
            <h2>{{ __('Payment::payment.sepay_review_title') }}</h2>
            <p>{{ __('Payment::payment.sepay_review_message') }}</p>
            <dl>
                <div><dt>{{ __('Payment::payment.sepay_payment_code') }}</dt><dd>{{ $paymentCode }}</dd></div>
            </dl>
            <a href="tel:19006067">1900 6067</a>
        </main>
        @include('core::partials.home.footer')
    </div>
@endsection