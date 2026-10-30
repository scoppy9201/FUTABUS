<x-core::side-panel id="bus-presentation-panel" @close="showPresentation = false" @click.self="showPresentation = false"
    x-effect="window.FutaSidePanel.sync($el, showPresentation, $nextTick)">
    <x-slot:title>{{ __('BusManagement::app.presentation_title') }}</x-slot:title>
    <form :action="presentationAction" method="POST" class="flex min-h-0 flex-1 flex-col">
        @csrf
        @method('PUT')
        <input type="hidden" name="presentation_bus_id" :value="presentation.id">
        <div class="min-h-0 flex-1 space-y-6 overflow-y-auto p-6">
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-base font-bold text-slate-900" x-text="presentation.license_plate"></p>
                <p class="mt-1 text-sm text-slate-500" x-text="presentation.vehicle_type"></p>
                <p class="mt-3 text-sm leading-6 text-slate-500">{{ __('BusManagement::app.presentation_hint') }}</p>
            </div>
            <fieldset>
                <legend class="mb-3 text-sm font-bold text-slate-900">{{ __('core::booking.vehicle_tab_amenities') }}</legend>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @foreach(\FuteBus\BusManagement\Services\BusPresentationService::AMENITIES as $amenity)
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white p-4">
                            <input type="checkbox" name="amenities[]" value="{{ $amenity }}" x-model="presentation.amenities"
                                class="size-4 shrink-0 accent-futa-orange">
                            <span class="text-sm font-semibold text-slate-700">{{ __('core::booking.amenity_'.$amenity) }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
            <div x-show="presentation.amenities.includes('wifi')" class="space-y-4">
                @foreach(['wifi_name', 'wifi_password'] as $field)
                    <div>
                        <label for="bus-{{ $field }}" class="mb-2 block text-sm font-semibold text-slate-700">{{ __('core::booking.'.$field) }}</label>
                        <input id="bus-{{ $field }}" type="text" name="{{ $field }}" maxlength="100" x-model="presentation.{{ $field }}"
                            :disabled="!presentation.amenities.includes('wifi')"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20">
                    </div>
                @endforeach
                <p class="text-xs leading-5 text-slate-500">{{ __('BusManagement::app.presentation_wifi_hint') }}</p>
            </div>
            @if($errors->any() && old('presentation_bus_id'))
                <p class="rounded-lg bg-red-50 p-3 text-sm text-red-700" role="alert">{{ $errors->first() }}</p>
            @endif
        </div>
        <footer class="flex shrink-0 justify-end gap-3 border-t border-slate-200 p-4">
            <button type="button" @click="showPresentation = false" class="rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold">
                {{ __('BusManagement::app.btn_cancel') }}
            </button>
            <button type="submit" class="rounded-xl bg-futa-orange px-5 py-3 text-sm font-bold text-white hover:bg-futa-orange-dark">
                {{ __('BusManagement::app.btn_save') }}
            </button>
        </footer>
    </form>
</x-core::side-panel>
