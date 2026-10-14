@extends('Dashboard::layouts.admin')

@section('title', __('BusManagement::app.bus_title'))

@section('content')
<div x-data="busManager()">

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-futa-orange">{{ $company?->name ?? 'FUTA Bus Lines' }}</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">{{ __('BusManagement::app.bus_title') }}</h1>
            <p class="mt-2 max-w-3xl text-sm font-medium text-slate-500">{{ __('BusManagement::app.bus_subtitle') }}</p>
        </div>
        <span class="rounded-full bg-futa-orange-soft px-4 py-2 text-sm font-bold text-futa-orange">
            {{ number_format($buses->total()) }} {{ __('Dashboard::app.records') }}
        </span>
    </div>


    {{-- Toolbar --}}
    <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" action="{{ route('bus-management.buses.index') }}" class="flex items-center gap-2">
            <div class="relative">
                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('BusManagement::app.bus_search_ph') }}"
                    class="w-72 rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-4 text-sm shadow-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20" />
            </div>
            <button type="submit" class="rounded-xl bg-futa-orange px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-futa-orange-dark">
                {{ __('BusManagement::app.btn_search') }}
            </button>
            @if($search)
                <a href="{{ route('bus-management.buses.index') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-500 hover:text-slate-700">
                    {{ __('BusManagement::app.btn_clear_filter') }}
                </a>
            @endif
        </form>
        <button @click="openAdd()" class="inline-flex items-center gap-2 rounded-xl bg-futa-orange px-5 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-futa-orange-dark hover:shadow-lg">
            <x-heroicon-o-plus class="size-4" /> {{ __('BusManagement::app.btn_add') }}
        </button>
    </div>

    {{-- Table --}}
    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="whitespace-nowrap px-5 py-4">{{ __('BusManagement::app.col_stt') }}</th>
                        <th class="whitespace-nowrap px-5 py-4">{{ __('BusManagement::app.bus_f_plate') }}</th>
                        <th class="whitespace-nowrap px-5 py-4">{{ __('BusManagement::app.bus_f_type') }}</th>
                        <th class="whitespace-nowrap px-5 py-4">{{ __('BusManagement::app.bus_f_brand') }}</th>
                        <th class="whitespace-nowrap px-5 py-4">{{ __('BusManagement::app.bus_f_year') }}</th>
                        <th class="whitespace-nowrap px-5 py-4">{{ __('BusManagement::app.bus_f_color') }}</th>
                        <th class="whitespace-nowrap px-5 py-4">{{ __('BusManagement::app.col_capacity') }}</th>
                        <th class="whitespace-nowrap px-5 py-4">{{ __('BusManagement::app.col_status') }}</th>
                        <th class="whitespace-nowrap px-5 py-4 text-center">{{ __('BusManagement::app.col_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($buses as $bus)
                        <tr class="hover:bg-futa-orange-soft/30">
                            <td class="px-5 py-4 text-slate-500">{{ $buses->firstItem() + $loop->index }}</td>
                            <td class="px-5 py-4 font-bold text-slate-900">{{ $bus->license_plate }}</td>
                            <td class="px-5 py-4">{{ $bus->vehicle_type_name ?? '—' }}</td>
                            <td class="px-5 py-4">{{ $bus->brand ?? '—' }}</td>
                            <td class="px-5 py-4">{{ $bus->manufacture_year ?? '—' }}</td>
                            <td class="px-5 py-4">{{ $bus->color ? (Illuminate\Support\Facades\Lang::has('BusManagement::app.bus_color_'.$bus->color) ? __('BusManagement::app.bus_color_'.$bus->color) : __('BusManagement::app.bus_color_other')) : '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-4">
                                {{ $bus->capacity }} {{ __('BusManagement::app.field_capacity_unit') }}
                            </td>
                            <td class="px-5 py-4">
                                @if($bus->status === 'active')
                                    <span class="inline-flex whitespace-nowrap rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">{{ __('BusManagement::app.status_active') }}</span>
                                @else
                                    <span class="inline-flex whitespace-nowrap rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">{{ __('BusManagement::app.status_inactive') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="openEdit({{ json_encode([
                                        'id' => $bus->id, 'license_plate' => $bus->license_plate, 'chassis_number' => $bus->chassis_number,
                                        'engine_number' => $bus->engine_number ?? '',
                                        'color' => $bus->color, 'brand' => $bus->brand, 'manufacture_year' => $bus->manufacture_year,
                                        'vehicle_type_id' => $bus->vehicle_type_id, 'description' => $bus->description, 'status' => $bus->status,
                                        'vehicle_type_name' => $bus->vehicle_type_name ?? '',
                                        'capacity' => $bus->capacity,
                                    ]) }})"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition hover:border-futa-orange hover:text-futa-orange">
                                        <x-heroicon-o-pencil-square class="size-3.5" /> {{ __('BusManagement::app.btn_edit') }}
                                    </button>
                                    @if($bus->status === 'active')
                                    <button @click="openDelete({{ json_encode(['id' => $bus->id, 'name' => $bus->license_plate]) }})"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-rose-600 shadow-sm transition hover:border-rose-300 hover:bg-rose-50">
                                        <x-heroicon-o-trash class="size-3.5" /> {{ __('BusManagement::app.btn_delete') }}
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-16 text-center text-sm font-semibold text-slate-500">
                                <x-core::empty-state :title="__('BusManagement::app.empty')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-4"><x-core::data-pagination :paginator="$buses" /></div>
    </div>

    {{-- Shared right-side panel --}}
    <x-core::side-panel id="buses-form-panel" @close="showForm = false" @click.self="showForm = false"
        x-effect="window.FutaSidePanel.sync($el, showForm, $nextTick)">
        <x-slot:title><span
                    x-text="isEdit ? @js(__('BusManagement::app.bus_modal_edit')) : @js(__('BusManagement::app.bus_modal_add'))"></span></x-slot:title>
        <form :action="formAction" method="POST" class="flex min-h-0 flex-1 flex-col">
                @csrf
                <div class="min-h-0 flex-1 space-y-5 overflow-y-auto px-6 py-6">
                <template x-if="isEdit"><input type="hidden" name="_method" value="PUT"></template>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    {{-- Biển số --}}
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">{{ __('BusManagement::app.bus_f_plate') }} <span class="text-rose-500">*</span></label>
                        <input type="text" name="license_plate" x-model="form.license_plate" required maxlength="20" :placeholder="@js(__('BusManagement::app.bus_plate_example'))"
                               @input="formatPlate($event)"
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20" />
                        <p class="mt-0.5 text-xs text-slate-400">{{ __('BusManagement::app.bus_plate_hint') }}</p>
                    </div>
                    {{-- Số khung --}}
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">{{ __('BusManagement::app.bus_f_chassis') }} <span class="text-rose-500">*</span></label>
                        <input type="text" name="chassis_number" x-model="form.chassis_number" required maxlength="17"
                               @input="form.chassis_number = $event.target.value.toUpperCase().replace(/[IOQ]/g,''); scheduleNotice('chassis', @js(__('BusManagement::app.bus_err_chassis_length')), form.chassis_number.length > 0 && form.chassis_number.length < 10)"
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20" />
                        <p class="mt-0.5 text-xs"
                           :class="form.chassis_number.length > 0 && form.chassis_number.length !== 17 ? 'text-amber-500' : 'text-slate-400'">
                            {{ __('BusManagement::app.bus_chassis_hint') }}
                            <span x-show="form.chassis_number.length > 0 && form.chassis_number.length !== 17"> · {{ __('BusManagement::app.bus_vin_standard_hint') }}</span>
                        </p>
                    </div>
                    {{-- Số máy --}}
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">{{ __('BusManagement::app.bus_engine_label') }} <span class="text-rose-500">*</span></label>
                        <input type="text" name="engine_number" x-model="form.engine_number" required minlength="6" maxlength="20"
                               @input="form.engine_number = $event.target.value.toUpperCase(); scheduleNotice('engine', @js(__('BusManagement::app.bus_err_engine_number')), form.engine_number.length > 0 && form.engine_number.length < 6)"
                               :placeholder="@js(__('BusManagement::app.bus_engine_example'))"
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20" />
                        <p class="mt-0.5 text-xs text-slate-400">{{ __('BusManagement::app.bus_engine_hint') }}</p>
                    </div>
                    {{-- Loại phương tiện --}}
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">{{ __('BusManagement::app.bus_f_type') }} <span class="text-rose-500">*</span></label>
                        <select name="vehicle_type_id" x-model="form.vehicle_type_id" required
                                @change="onTypeChange()"
                                class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20">
                            <option value="">{{ __('BusManagement::app.bus_f_type_select') }}</option>
                            @foreach($vehicleTypes as $vt)
                                <option value="{{ $vt->id }}" data-capacity="{{ $vt->default_capacity }}" data-name="{{ $vt->name }}" data-unit="{{ __('BusManagement::app.vt_unit_'.$vt->vehicle_class) }}">{{ $vt->name }}</option>
                            @endforeach
                        </select>
                        {{-- Thông tin kế thừa --}}
                        <div x-show="inheritedInfo" class="mt-1 rounded-xl bg-futa-orange-soft px-3 py-1.5 text-sm font-semibold text-futa-orange" x-text="inheritedInfo"></div>
                    </div>
                    {{-- Hãng xe --}}
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">{{ __('BusManagement::app.bus_f_brand') }} <span class="text-rose-500">*</span></label>
                        <input type="text" name="brand" x-model="form.brand" required maxlength="100"
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20" />
                    </div>
                    {{-- Màu xe --}}
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">{{ __('BusManagement::app.bus_f_color') }} <span class="text-rose-500">*</span></label>
                        <select name="color" x-model="form.color" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20">
                            <option value="">{{ __('BusManagement::app.bus_color_select') }}</option>
                            @foreach($vehicleColors as $color)<option value="{{ $color }}">{{ __('BusManagement::app.bus_color_'.$color) }}</option>@endforeach
                        </select>
                    </div>
                    {{-- Năm sản xuất --}}
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">{{ __('BusManagement::app.bus_f_year') }} <span class="text-rose-500">*</span></label>
                        <input type="number" name="manufacture_year" x-model="form.manufacture_year" required min="1990" max="{{ date('Y') }}"
                               @input="scheduleNotice('year', @js(__('BusManagement::app.bus_err_year_range', ['min' => 1990, 'max' => date('Y')])), String(form.manufacture_year).length === 4 && (form.manufacture_year < 1990 || form.manufacture_year > {{ date('Y') }}))"
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20" />
                        <p class="mt-0.5 text-xs text-slate-400">{{ __('BusManagement::app.bus_year_hint', ['min' => 1990, 'max' => date('Y')]) }}</p>
                    </div>
                </div>

                <div x-show="isEdit">
                    <label class="mb-1 block text-sm font-semibold text-slate-700">{{ __('BusManagement::app.col_status') }}</label>
                    <select name="status" x-model="form.status" :disabled="!isEdit"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm">
                        <option value="active">{{ __('BusManagement::app.status_active') }}</option>
                        <option value="inactive">{{ __('BusManagement::app.status_inactive') }}</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">{{ __('BusManagement::app.field_description') }}</label>
                    <textarea name="description" x-model="form.description" rows="2" maxlength="500"
                              class="w-full resize-none rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20"></textarea>
                </div>

                </div>
                <footer class="flex shrink-0 items-center justify-end gap-3 border-t border-slate-200 bg-white px-6 py-4">
                    <button type="button" @click="showForm = false" class="rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('BusManagement::app.btn_cancel') }}</button>
                    <button type="submit" class="rounded-xl bg-futa-orange px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-futa-orange-dark"
                            x-text="isEdit ? @js(__('BusManagement::app.btn_save')) : @js(__('BusManagement::app.btn_store'))"></button>
                </footer>
            </form>
    </x-core::side-panel>

    {{-- DELETE MODAL --}}
    <form x-ref="deleteForm" :action="deleteAction" method="POST" hidden data-confirm
        :data-confirm-title="@js(__('BusManagement::app.bus_modal_delete'))"
        :data-confirm-message="@js(__('BusManagement::app.bus_delete_msg')) + ' ' + deleteData.name + '?'"
        data-confirm-label="{{ __('BusManagement::app.btn_confirm_delete') }}">
        @csrf
        @method('DELETE')
    </form>
</div>

<script>
function busManager() {
    // Bảng tra nhanh: vehicle_type_id → {capacity, name}
    const vtMap = {};
    @foreach($vehicleTypes as $vt)
                vtMap[{{ $vt->id }}] = { capacity: {{ $vt->default_capacity }}, name: @js($vt->name), unit: @js(__('BusManagement::app.vt_unit_'.$vt->vehicle_class)) };
    @endforeach

    const blank = {
        license_plate: '', chassis_number: '', engine_number: '',
        color: '', brand: '', manufacture_year: '',
        vehicle_type_id: '', description: '', status: 'active'
    };
    const base = `{{ url('quan-tri/phuong-tien/thong-tin-xe') }}`;

    return {
        showForm: false, isEdit: false,
        form: { ...blank }, formAction: base,
        deleteData: {}, deleteAction: '',
        inheritedInfo: '',
        noticeTimers: {},

        scheduleNotice(field, message, invalid) {
            clearTimeout(this.noticeTimers[field]);
            const key = `bus-field-${field}`;
            if (!invalid) {
                window.FutaNotify?.close(key);
                return;
            }
            this.noticeTimers[field] = setTimeout(() => window.FutaNotify?.warning(message, { key }), 500);
        },

        onTypeChange() {
            const vt = vtMap[this.form.vehicle_type_id];
            if (vt) {
                this.inheritedInfo = @js(__('BusManagement::app.bus_capacity_inherited', ['capacity' => '__CAP__', 'unit' => '__UNIT__', 'name' => '__NAME__']))
                    .replace('__CAP__', vt.capacity).replace('__UNIT__', vt.unit).replace('__NAME__', vt.name);
            } else {
                this.inheritedInfo = '';
            }
        },

        formatPlate(event) {
            let v = event.target.value.toUpperCase().replace(/[^A-Z0-9.\-]/g, '');
            // Tự định dạng 51B12345 → 51B-123.45 khi gõ đủ
            const raw = v.replace(/[-\.]/g, '');
            if (/^[0-9]{2}[A-Z][0-9]{4}$/.test(raw)) {
                v = `${raw.slice(0, 3)}-${raw.slice(3)}`;
            } else if (/^[0-9]{2}[A-Z]{1,2}[0-9]{5}$/.test(raw)) {
                const letters = raw.match(/^[0-9]{2}[A-Z]{1,2}/)[0];
                const digits  = raw.slice(letters.length);
                v = `${letters}-${digits.slice(0,3)}.${digits.slice(3)}`;
            }
            this.form.license_plate = v;
            event.target.value = v;
            this.scheduleNotice('plate', @js(__('BusManagement::app.bus_err_plate_format')), v.length >= 8 && !/^([0-9]{2}[A-Z]{1,2}-[0-9]{3}\.[0-9]{2}|[0-9]{2}[A-Z]-[0-9]{4})$/.test(v));
        },

        openAdd() {
            this.isEdit = false;
            this.form = { ...blank };
            this.formAction = base;
            this.inheritedInfo = '';
            this.showForm = true;
        },
        openEdit(d) {
            this.isEdit = true;
            this.form = { ...blank, ...d, color: ['white', 'black', 'silver', 'gray', 'red', 'blue', 'green', 'yellow', 'orange', 'other'].includes(d.color) ? d.color : 'other', engine_number: d.engine_number ?? '', description: d.description ?? '' };
            this.formAction = `${base}/${d.id}`;
            const vt = vtMap[d.vehicle_type_id];
            this.inheritedInfo = vt ? @js(__('BusManagement::app.bus_capacity_inherited', ['capacity' => '__CAP__', 'unit' => '__UNIT__', 'name' => '__NAME__']))
                .replace('__CAP__', vt.capacity).replace('__UNIT__', vt.unit).replace('__NAME__', vt.name) : '';
            this.showForm = true;
        },
        openDelete(d) {
            this.deleteData = d;
            this.deleteAction = `${base}/${d.id}`;
            this.$nextTick(() => this.$refs.deleteForm.requestSubmit());
        },
    };
}
</script>
@include('BusManagement::partials.notifications')
@endsection
