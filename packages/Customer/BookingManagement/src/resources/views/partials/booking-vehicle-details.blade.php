@php
    $vehicle = ($vehicleLeg === 'outbound' ? $outboundTrip : $trip)['vehicle_details'];
    $fields = [
        'vehicle_type' => 'vehicle_type', 'license_plate' => 'vehicle_plate', 'brand' => 'vehicle_brand',
        'color' => 'vehicle_color', 'manufacture_year' => 'vehicle_year', 'capacity' => 'vehicle_capacity', 'floors' => 'vehicle_floors',
    ];
@endphp
<div class="space-y-4 p-4 sm:p-5">
    <div class="flex items-center gap-3">
        <span class="grid size-10 place-items-center rounded-xl bg-futa-orange-soft text-futa-orange">
            <x-heroicon-o-truck class="size-6" aria-hidden="true" />
        </span>
        <h3 class="text-base font-bold text-slate-900">{{ $vehicle['vehicle_type'] ?: __('core::booking.vehicle_info') }}</h3>
    </div>
    <dl class="divide-y divide-slate-100 rounded-xl border border-slate-200 bg-white px-4">
        @foreach($fields as $field => $label)
            @if($vehicle[$field] !== null && $vehicle[$field] !== '')
                <div class="flex items-start justify-between gap-4 py-3 text-sm">
                    <dt class="shrink-0 text-slate-500">{{ __('core::booking.'.$label) }}</dt>
                    <dd class="text-right font-medium text-slate-900">
                        @if($field === 'color')
                            {{ \Illuminate\Support\Facades\Lang::has('BusManagement::app.bus_color_'.$vehicle[$field])
                                ? __('BusManagement::app.bus_color_'.$vehicle[$field]) : __('BusManagement::app.bus_color_other') }}
                        @else
                            {{ $vehicle[$field] }}
                            @if($field === 'capacity'){{ __('core::booking.vehicle_'.$vehicle['capacity_unit']) }}@endif
                        @endif
                    </dd>
                </div>
            @endif
        @endforeach
    </dl>
</div>
