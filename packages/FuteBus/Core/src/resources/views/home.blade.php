@extends('core::layouts.home')

@section('content')
    <div class="home-page">
        @include('core::partials.home.navbar')
        @include('core::partials.home.hero')
        @include('core::partials.home.promotions')
        @include('core::partials.home.popular-routes')
        @include('core::partials.home.service-quality')
        @include('core::partials.home.latest-news')
        @include('core::partials.home.futa-ecosystem')

        @include('core::partials.home.footer')
    </div>
@endsection
