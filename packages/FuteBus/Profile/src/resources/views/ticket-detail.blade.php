@extends('core::layouts.home')

@section('title', __('Profile::tickets.detail_title'))

@section('content')
    <div class="home-page min-h-screen bg-white">
        @include('core::partials.home.navbar')

        <main class="mx-auto grid w-full max-w-282 gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[255px_minmax(0,1fr)] lg:px-0">
            @include('Profile::partials.account-sidebar')

            <section class="min-w-0" aria-labelledby="ticket-detail-title">
                <a href="{{ route('profile.tickets.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-futa-orange hover:underline">
                    <x-heroicon-o-arrow-left class="size-4" />
                    {{ __('Profile::tickets.back_to_history') }}
                </a>
                <h1 id="ticket-detail-title" class="mt-4 text-3xl font-semibold text-gray-950">{{ __('Profile::tickets.detail_title') }}</h1>
                <p class="mt-2 text-base font-medium text-slate-600">{{ __('Profile::tickets.detail_description') }}</p>

                <div class="mt-7 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-futa-orange-soft bg-futa-orange-soft/50 px-6 py-5">
                        <p class="text-sm font-semibold text-slate-600">{{ __('Profile::tickets.columns.code') }}</p>
                        <p class="mt-1 text-xl font-bold text-futa-orange">{{ $booking->booking_code }}</p>
                        @if(str_starts_with($booking->booking_code, 'DEMOHIST'))
                            <p class="mt-1 text-sm font-medium text-slate-600">{{ __('Profile::tickets.demo_label') }}</p>
                        @endif
                    </div>
                    <dl class="grid gap-x-8 gap-y-6 p-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-slate-600">{{ __('Profile::tickets.columns.route') }}</dt>
                            <dd class="mt-1 font-semibold text-gray-950">{{ $booking->origin_city }} → {{ $booking->destination_city }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-slate-600">{{ __('Profile::tickets.columns.departure') }}</dt>
                            <dd class="mt-1 font-semibold text-gray-950">{{ \Illuminate\Support\Carbon::parse($booking->departure_time)->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-slate-600">{{ __('Profile::tickets.columns.count') }}</dt>
                            <dd class="mt-1 font-semibold text-gray-950">{{ $booking->seat_count }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-slate-600">{{ __('Profile::tickets.columns.amount') }}</dt>
                            <dd class="mt-1 font-semibold text-gray-950">
                                {{ __('Profile::tickets.currency', ['amount' => number_format((float) $booking->total_amount, 0, ',', '.')]) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-slate-600">{{ __('Profile::tickets.columns.payment') }}</dt>
                            <dd class="mt-1 font-semibold text-gray-950">{{ __('Profile::tickets.payment_status.'.($booking->payment_status ?? 'unpaid')) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-slate-600">{{ __('Profile::tickets.columns.status') }}</dt>
                            <dd class="mt-1 font-semibold text-gray-950">{{ __('Profile::tickets.booking_status.'.$booking->status) }}</dd>
                        </div>
                    </dl>
                </div>
            </section>
        </main>

        @include('core::partials.home.footer')
    </div>
@endsection
