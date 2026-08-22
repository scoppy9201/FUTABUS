@extends('core::layouts.home')

@section('content')
    @include('core::partials.home.navbar')
    @include('core::partials.home.hero')
    @include('core::partials.home.promotions')
    @include('core::partials.home.popular-routes')
    @include('core::partials.home.service-quality')

    {{-- Footer --}}
    <footer class="bg-gray-900 py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center text-sm text-gray-400">
                &copy; {{ date('Y') }} {{ __('core::app.home.footer.copyright') }}
            </div>
        </div>
    </footer>
@endsection
