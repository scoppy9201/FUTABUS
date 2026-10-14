@php
    $input = 'w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none focus:border-futa-orange focus:ring-2 focus:ring-futa-orange/20';
    $fields = $kind === 'route'
        ? ['code', 'origin_city', 'destination_city', 'distance_km', 'base_price', 'vehicle_type', 'is_active']
        : ['name', 'address', 'position', 'leg_minutes', 'stop_type'];
    $numeric = ['distance_km' => [1, 10000], 'base_price' => [1001, 99999999], 'position' => [1, 1000], 'leg_minutes' => [0, 1440]];
    $limits = ['code' => 40, 'origin_city' => 100, 'destination_city' => 100, 'name' => 150, 'address' => 255];
    $choices = [
        'vehicle_type' => __('RouteManagement::app.vehicles'),
        'stop_type' => __('RouteManagement::app.types'),
        'is_active' => [1 => __('RouteManagement::app.active'), 0 => __('RouteManagement::app.inactive')],
    ];
    $createLabel = __('RouteManagement::app.'.($kind === 'route' ? 'create' : 'add_stop'));
    $editLabel = __('RouteManagement::app.'.($kind === 'route' ? 'edit' : 'edit_stop'));
    $base = $kind === 'route' ? route('route-management.store') : route('route-management.stops.store', $route->id);
    $initial = collect(old())->only([...$fields, 'next_leg_minutes'])->all() + ['stop_order' => (int) old('_original_position')];
@endphp
<x-core::side-panel :id="$kind.'-form-panel'" :title="$createLabel" :description="__('RouteManagement::app.'.($kind === 'route' ? 'route_hint' : 'stop_hint'))"
    data-record-panel="{{ $kind }}" data-create-title="{{ $createLabel }}" data-edit-title="{{ $editLabel }}">
    <form method="post" action="{{ $base }}" data-record-form data-base="{{ $base }}" data-position="{{ $kind === 'stop' ? $stops->count() + 1 : 0 }}"
        data-reopen="{{ $errors->any() && old('_panel') === $kind ? '1' : '0' }}" data-initial="{{ json_encode($initial) }}" data-record-id="{{ (int) old('_record') }}"
        class="flex min-h-0 flex-1 flex-col">
        @csrf
        <input type="hidden" name="_method" value="PUT" disabled>
        <input type="hidden" name="_panel" value="{{ $kind }}">
        <input type="hidden" name="_record" value="">
        @if($kind === 'stop')<input type="hidden" name="_original_position" value="">@endif
        <div class="min-h-0 flex-1 overflow-y-auto px-6 py-6">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            @foreach($fields as $field)
                <div @class(['min-w-0', 'sm:col-span-2' => in_array($field, ['code', 'name', 'address', 'stop_type'])])>
                    <label for="{{ $kind }}-{{ $field }}" class="mb-2 block text-sm font-semibold text-slate-700">
                        {{ __('RouteManagement::app.fields.'.$field) }} <span class="text-rose-500">*</span>
                    </label>
                    @if(isset($choices[$field]))
                        <select id="{{ $kind }}-{{ $field }}" name="{{ $field }}" class="{{ $input }}" required>
                            @foreach($choices[$field] as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                        </select>
                    @else
                        <input id="{{ $kind }}-{{ $field }}" name="{{ $field }}" type="{{ isset($numeric[$field]) ? 'number' : 'text' }}" required class="{{ $input }}"
                            @if(isset($numeric[$field])) min="{{ $numeric[$field][0] }}" max="{{ $numeric[$field][1] }}" step="1" @endif
                            @if(isset($limits[$field])) maxlength="{{ $limits[$field] }}" @endif
                            @if($field === 'code') pattern="[A-Za-z0-9]+(?:-[A-Za-z0-9]+)*" @endif>
                    @endif
                    @error($field)<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                </div>
            @endforeach
            @if($kind === 'stop')
                <div data-next-leg-field hidden class="sm:col-span-2">
                    <label for="next-leg-minutes" class="mb-2 block text-sm font-semibold text-slate-700">{{ __('RouteManagement::app.next_leg_label') }}</label>
                    <input id="next-leg-minutes" name="next_leg_minutes" type="number" min="1" max="1440" step="1" disabled class="{{ $input }}">
                    <p class="mt-2 text-xs leading-5 text-slate-500">{{ __('RouteManagement::app.next_leg_hint') }}</p>
                    @error('next_leg_minutes')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            @endif
            </div>
        </div>
        <footer class="flex shrink-0 justify-end gap-3 border-t border-slate-200 px-6 py-4">
            <button type="button" data-panel-close class="rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-600">{{ __('RouteManagement::app.cancel') }}</button>
            <button class="rounded-xl bg-futa-orange px-5 py-3 text-sm font-bold text-white">{{ __('RouteManagement::app.save') }}</button>
        </footer>
    </form>
</x-core::side-panel>
