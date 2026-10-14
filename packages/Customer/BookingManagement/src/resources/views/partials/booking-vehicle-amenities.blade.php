@php
    $vehicle = ($vehicleLeg === 'outbound' ? $outboundTrip : $trip)['vehicle_details'];
    $icons = ['blanket' => 'squares-2x2', 'water' => 'beaker', 'wet_towel' => 'sparkles', 'charging' => 'bolt', 'wifi' => 'wifi'];
@endphp
<div class="space-y-4 p-4 sm:p-5">
    @if($vehicle['amenities'])
        <p class="text-sm leading-6 text-slate-500">{{ __('core::booking.amenities_hint') }}</p>
        <ul class="grid grid-cols-2 gap-3">
            @foreach($vehicle['amenities'] as $amenity)
                <li class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-4">
                    <x-dynamic-component :component="'heroicon-o-'.$icons[$amenity]" class="size-5 shrink-0 text-futa-green" aria-hidden="true" />
                    <span class="text-sm font-medium text-slate-900">{{ __('core::booking.amenity_'.$amenity) }}</span>
                </li>
            @endforeach
        </ul>
        @if(in_array('wifi', $vehicle['amenities'], true) && ($vehicle['wifi_name'] || $vehicle['wifi_password']))
            <div class="rounded-xl border border-green-100 bg-green-50 p-4 text-sm">
                @foreach(['wifi_name', 'wifi_password'] as $field)
                    @if($vehicle[$field])
                        <div class="flex flex-wrap items-center justify-between gap-2 py-1">
                            <span class="text-slate-600">{{ __('core::booking.'.$field) }}</span>
                            <span class="break-all font-semibold text-futa-green">{{ $vehicle[$field] }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    @else
        <x-core::empty-state :title="__('core::booking.amenities_empty')" compact />
    @endif
</div>
