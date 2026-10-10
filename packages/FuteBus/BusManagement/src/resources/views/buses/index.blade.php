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
        <span class="rounded-full bg-futa-orange-soft px-4 py-2 text-sm font-bold text-futa-orange-dark">
            {{ number_format($buses->total()) }} {{ __('Dashboard::app.records') }}
        </span>
    </div>

    @if(session('success'))
        <div class="mt-4 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            <x-heroicon-o-check-circle class="size-5 shrink-0" /> {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
            <ul class="list-inside list-disc space-y-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- Toolbar --}}
    <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" action="{{ route('bus-management.buses.index') }}" class="flex items-center gap-2">
            <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('BusManagement::app.bus_search_ph') }}"
                class="w-72 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20" />
            <button type="submit" class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200">{{ __('BusManagement::app.btn_search') }}</button>
            @if($search)
                <a href="{{ route('bus-management.buses.index') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-500">{{ __('BusManagement::app.btn_clear_filter') }}</a>
            @endif
        </form>
        <button @click="openAdd()" class="inline-flex items-center gap-2 rounded-xl bg-futa-orange px-5 py-2.5 text-sm font-bold text-white shadow-md hover:opacity-90">
            <x-heroicon-o-plus class="size-4" /> {{ __('BusManagement::app.btn_add') }}
        </button>
    </div>

    {{-- Table --}}
    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-225 text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-4">{{ __('BusManagement::app.col_stt') }}</th>
                        <th class="px-5 py-4">{{ __('BusManagement::app.bus_f_plate') }}</th>
                        <th class="px-5 py-4">{{ __('BusManagement::app.bus_f_type') }}</th>
                        <th class="px-5 py-4">{{ __('BusManagement::app.bus_f_brand') }}</th>
                        <th class="px-5 py-4">{{ __('BusManagement::app.bus_f_year') }}</th>
                        <th class="px-5 py-4">{{ __('BusManagement::app.bus_f_color') }}</th>
                        <th class="px-5 py-4">{{ __('BusManagement::app.col_capacity') }}</th>
                        <th class="px-5 py-4">{{ __('BusManagement::app.col_status') }}</th>
                        <th class="px-5 py-4 text-center">{{ __('BusManagement::app.col_actions') }}</th>
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
                            <td class="px-5 py-4">{{ $bus->color ?? '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-4">{{ $bus->seat_rows }}×{{ $bus->seat_columns }} = {{ $bus->capacity }} {{ __('BusManagement::app.field_capacity_unit') }}</td>
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
                                        'color' => $bus->color, 'brand' => $bus->brand, 'manufacture_year' => $bus->manufacture_year,
                                        'vehicle_type_id' => $bus->vehicle_type_id, 'seat_rows' => $bus->seat_rows,
                                        'seat_columns' => $bus->seat_columns, 'description' => $bus->description, 'status' => $bus->status,
                                    ]) }})" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-futa-orange hover:text-futa-orange">
                                        <x-heroicon-o-pencil-square class="size-3.5" /> {{ __('BusManagement::app.btn_edit') }}
                                    </button>
                                    @if($bus->status === 'active')
                                    <button @click="openDelete({{ json_encode(['id' => $bus->id, 'name' => $bus->license_plate]) }})"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-rose-600 hover:border-rose-300 hover:bg-rose-50">
                                        <x-heroicon-o-trash class="size-3.5" /> {{ __('BusManagement::app.btn_delete') }}
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-6 py-16 text-center text-sm font-semibold text-slate-500">{{ __('BusManagement::app.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($buses->hasPages())
            <div class="border-t border-slate-100 px-5 py-4">{{ $buses->links() }}</div>
        @endif
    </div>

    {{-- ADD / EDIT MODAL (dùng chung 1 form) --}}
    <div x-show="showForm" x-cloak @click.self="showForm = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm" style="display:none">
        <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between px-5 py-3"
                 :class="isEdit ? 'bg-slate-800' : 'bg-futa-orange'">
                <h2 class="text-base font-bold text-white"
                    x-text="isEdit ? @js(__('BusManagement::app.bus_modal_edit')) : @js(__('BusManagement::app.bus_modal_add'))"></h2>
                <button type="button" @click="showForm = false" class="text-white/70 hover:text-white"><x-heroicon-o-x-mark class="size-4" /></button>
            </div>

            <form :action="formAction" method="POST" class="space-y-3 p-5">
                @csrf
                <template x-if="isEdit"><input type="hidden" name="_method" value="PUT"></template>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('BusManagement::app.bus_f_plate') }} <span class="text-rose-500">*</span></label>
                        <input type="text" name="license_plate" x-model="form.license_plate" required maxlength="20" placeholder="51B-123.45"
                               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('BusManagement::app.bus_f_chassis') }} <span class="text-rose-500">*</span></label>
                        <input type="text" name="chassis_number" x-model="form.chassis_number" required maxlength="50"
                               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('BusManagement::app.bus_f_type') }} <span class="text-rose-500">*</span></label>
                        <select name="vehicle_type_id" x-model="form.vehicle_type_id" required
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20">
                            <option value="">{{ __('BusManagement::app.bus_f_type_select') }}</option>
                            @foreach($vehicleTypes as $vt)
                                <option value="{{ $vt->id }}">{{ $vt->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('BusManagement::app.bus_f_brand') }} <span class="text-rose-500">*</span></label>
                        <input type="text" name="brand" x-model="form.brand" required maxlength="100"
                               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('BusManagement::app.bus_f_color') }} <span class="text-rose-500">*</span></label>
                        <input type="text" name="color" x-model="form.color" required maxlength="50"
                               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('BusManagement::app.bus_f_year') }} <span class="text-rose-500">*</span></label>
                        <input type="number" name="manufacture_year" x-model="form.manufacture_year" required min="1990" max="{{ date('Y') + 1 }}"
                               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('BusManagement::app.bus_f_rows') }} <span class="text-rose-500">*</span></label>
                        <input type="number" name="seat_rows" x-model.number="form.seat_rows" required min="1" max="30"
                               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('BusManagement::app.bus_f_cols') }} <span class="text-rose-500">*</span></label>
                        <input type="number" name="seat_columns" x-model.number="form.seat_columns" required min="1" max="10"
                               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20" />
                    </div>
                </div>

                <p class="text-xs font-semibold text-slate-500">
                    {{ __('BusManagement::app.col_capacity') }}:
                    <span class="text-futa-orange" x-text="(form.seat_rows * form.seat_columns || 0) + ' {{ __('BusManagement::app.field_capacity_unit') }}'"></span>
                </p>

                <div x-show="isEdit">
                    <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('BusManagement::app.col_status') }}</label>
                    <select name="status" x-model="form.status" :disabled="!isEdit"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        <option value="active">{{ __('BusManagement::app.status_active') }}</option>
                        <option value="inactive">{{ __('BusManagement::app.status_inactive') }}</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('BusManagement::app.field_description') }}</label>
                    <textarea name="description" x-model="form.description" rows="2" maxlength="500"
                              class="w-full resize-none rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                    <button type="button" @click="showForm = false" class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">{{ __('BusManagement::app.btn_cancel') }}</button>
                    <button type="submit" class="rounded-lg bg-futa-orange px-5 py-2 text-xs font-bold text-white shadow-sm hover:bg-futa-orange-dark"
                            x-text="isEdit ? @js(__('BusManagement::app.btn_save')) : @js(__('BusManagement::app.btn_store'))"></button>
                </div>
            </form>
        </div>
    </div>

    {{-- DELETE MODAL --}}
    <div x-show="showDelete" x-cloak @click.self="showDelete = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm" style="display:none">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 text-center shadow-2xl">
            <div class="mx-auto mb-4 grid size-14 place-items-center rounded-full bg-rose-100">
                <x-heroicon-o-exclamation-triangle class="size-7 text-rose-500" />
            </div>
            <h3 class="text-lg font-bold text-slate-900">{{ __('BusManagement::app.bus_modal_delete') }}</h3>
            <p class="mt-2 text-sm text-slate-500">
                {{ __('BusManagement::app.bus_delete_msg') }}
                <span class="font-bold text-slate-800" x-text="deleteData.name"></span>?
            </p>
            <form :action="deleteAction" method="POST" class="mt-5 flex items-center justify-end gap-2">
                @csrf
                @method('DELETE')
                <button type="button" @click="showDelete = false" class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">{{ __('BusManagement::app.btn_cancel') }}</button>
                <button type="submit" class="rounded-lg bg-rose-600 px-5 py-2 text-xs font-bold text-white hover:bg-rose-700">{{ __('BusManagement::app.btn_confirm_delete') }}</button>
            </form>
        </div>
    </div>
</div>

<script>
function busManager() {
    const blank = { license_plate:'', chassis_number:'', color:'', brand:'', manufacture_year:'',
                    vehicle_type_id:'', seat_rows:10, seat_columns:4, description:'', status:'active' };
    const base = `{{ url('quan-tri/phuong-tien/thong-tin-xe') }}`;
    return {
        showForm: false, showDelete: false, isEdit: false,
        form: { ...blank }, formAction: base,
        deleteData: {}, deleteAction: '',
        openAdd()  { this.isEdit = false; this.form = { ...blank }; this.formAction = base; this.showForm = true; },
        openEdit(d){ this.isEdit = true; this.form = { ...blank, ...d, description: d.description ?? '' }; this.formAction = `${base}/${d.id}`; this.showForm = true; },
        openDelete(d){ this.deleteData = d; this.deleteAction = `${base}/${d.id}`; this.showDelete = true; },
    };
}
</script>
@endsection