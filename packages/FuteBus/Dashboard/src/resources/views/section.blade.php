@extends('Dashboard::layouts.admin')

@section('title', __('Dashboard::app.'.$section))
@section('page_title', __('Dashboard::app.'.$section))

@section('content')
    @php
        $columns = match ($section) {
            'trips' => ['route', 'departure', 'vehicle', 'available_seats', 'price', 'status_label'],
            'routes' => ['code', 'origin', 'destination', 'base_price', 'status_label'],
            'buses' => ['license_plate', 'vehicle', 'bus_type', 'capacity', 'status_label'],
            'bookings' => ['booking_code', 'customer', 'route', 'seats', 'amount', 'status_label'],
            'customers' => ['customer', 'email', 'phone', 'booking_count'],
            'reports' => ['date', 'booking_count', 'booking_value'],
        };
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#ef5222]">{{ $company?->name ?? 'FUTA Bus Lines' }}</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">{{ __('Dashboard::app.'.$section) }}</h1>
            <p class="mt-2 max-w-3xl text-sm font-medium text-slate-500 sm:text-base">{{ __('Dashboard::app.section_hint.'.$section) }}</p>
        </div>
        <span class="rounded-full bg-orange-50 px-4 py-2 text-sm font-bold text-[#d7461a]">
            {{ number_format($rows->total()) }} {{ __('Dashboard::app.records') }}
        </span>
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-170 text-left text-sm">
                <caption class="sr-only">{{ __('Dashboard::app.'.$section) }}</caption>
                <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                    <tr>
                        @foreach($columns as $column)
                            <th scope="col" class="whitespace-nowrap px-5 py-4">{{ __('Dashboard::app.'.$column) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rows as $row)
                        <tr class="hover:bg-orange-50/30">
                            @switch($section)
                                @case('trips')
                                    <td class="px-5 py-4 font-bold text-slate-900">{{ $row->origin_city }} → {{ $row->destination_city }}</td>
                                    <td class="whitespace-nowrap px-5 py-4">{{ \Illuminate\Support\Carbon::parse($row->departure_time)->format('H:i · d/m/Y') }}</td>
                                    <td class="px-5 py-4">{{ $row->license_plate ?? '—' }}</td>
                                    <td class="px-5 py-4">{{ $row->available_seats ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4">{{ number_format($row->price, 0, ',', '.') }} ₫</td>
                                    <td class="px-5 py-4">@include('Dashboard::partials.status', ['value' => $row->status])</td>
                                    @break
                                @case('routes')
                                    <td class="px-5 py-4 font-bold text-slate-900">{{ $row->code }}</td>
                                    <td class="px-5 py-4">{{ $row->origin_city }}</td>
                                    <td class="px-5 py-4">{{ $row->destination_city }}</td>
                                    <td class="whitespace-nowrap px-5 py-4">{{ number_format($row->base_price, 0, ',', '.') }} ₫</td>
                                    <td class="px-5 py-4">@include('Dashboard::partials.status', ['value' => $row->is_active ? 'active' : 'inactive'])</td>
                                    @break
                                @case('buses')
                                    <td class="px-5 py-4 font-bold text-slate-900">{{ $row->license_plate }}</td>
                                    <td class="px-5 py-4">{{ $row->name ?: '—' }}</td>
                                    <td class="px-5 py-4">{{ __('Dashboard::app.bus_types.'.$row->bus_type) }}</td>
                                    <td class="px-5 py-4">{{ $row->capacity }} {{ __('Dashboard::app.seats') }}</td>
                                    <td class="px-5 py-4">@include('Dashboard::partials.status', ['value' => $row->status])</td>
                                    @break
                                @case('bookings')
                                    <td class="px-5 py-4 font-bold text-slate-900">{{ $row->booking_code }}</td>
                                    <td class="px-5 py-4">{{ $row->full_name }}</td>
                                    <td class="px-5 py-4">{{ $row->origin_city }} → {{ $row->destination_city }}</td>
                                    <td class="px-5 py-4">{{ $row->seat_count }}</td>
                                    <td class="whitespace-nowrap px-5 py-4">{{ number_format($row->total_amount, 0, ',', '.') }} ₫</td>
                                    <td class="px-5 py-4">@include('Dashboard::partials.status', ['value' => $row->status])</td>
                                    @break
                                @case('customers')
                                    <td class="px-5 py-4 font-bold text-slate-900">{{ $row->full_name }}</td>
                                    <td class="px-5 py-4">{{ $row->email ?: '—' }}</td>
                                    <td class="px-5 py-4">{{ $row->phone }}</td>
                                    <td class="px-5 py-4">{{ $row->booking_count }}</td>
                                    @break
                                @case('reports')
                                    <td class="px-5 py-4 font-bold text-slate-900">{{ \Illuminate\Support\Carbon::parse($row->booking_date)->format('d/m/Y') }}</td>
                                    <td class="px-5 py-4">{{ $row->booking_count }}</td>
                                    <td class="whitespace-nowrap px-5 py-4">{{ number_format($row->booking_value, 0, ',', '.') }} ₫</td>
                                    @break
                            @endswitch
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($columns) }}" class="px-6 py-16 text-center">
                                <span class="mx-auto grid size-14 place-items-center rounded-full bg-slate-50 text-slate-300"><x-heroicon-o-inbox class="size-8" /></span>
                                <p class="mt-3 text-sm font-semibold text-slate-500">{{ __('Dashboard::app.empty') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($rows->hasPages())
            <div class="border-t border-slate-100 px-5 py-4">{{ $rows->links() }}</div>
        @endif
    </div>
@endsection
