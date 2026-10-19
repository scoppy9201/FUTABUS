@extends('core::layouts.home')

@push('styles')
    @vite('packages/Customer/TripSearch/src/resources/css/app.css')
@endpush

@section('content')
    <x-core::landing-background>
        @include('core::partials.home.navbar')
        @include('core::partials.home.hero')
        @include('core::partials.home.promotions')
        @include('core::partials.home.popular-routes')
        @include('core::partials.home.service-quality')
        @include('core::partials.home.latest-news')
        @include('core::partials.home.futa-ecosystem')

        @include('core::partials.home.footer')
    </x-core::landing-background>

    @include('core::partials.home.chatbot')
@endsection
