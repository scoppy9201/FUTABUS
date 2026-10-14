@extends('Dashboard::layouts.admin')

@section('title', __('BusManagement::app.vehicle_types_title'))

@section('content')
@php
    $classes = $classes ?? [];
    $inp = 'w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-800 placeholder-slate-400 transition focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20';
    $inpDisabled = $inp . ' bg-slate-50 text-slate-500 cursor-not-allowed';
@endphp
<div x-data="vehicleTypeManager()">
    @include('BusManagement::vehicle-types.partials.toolbar')

    {{-- ── Table ── --}}
    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1000px] text-left text-sm">
                <caption class="sr-only">{{ __('BusManagement::app.vehicle_types_title') }}</caption>
                <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="whitespace-nowrap px-5 py-4">{{ __('BusManagement::app.col_stt') }}</th>
                        <th class="whitespace-nowrap px-5 py-4">{{ __('BusManagement::app.vt_name_label') }}</th>
                        <th class="whitespace-nowrap px-5 py-4">{{ __('BusManagement::app.vt_class_label') }}</th>
                        <th class="whitespace-nowrap px-5 py-4">{{ __('BusManagement::app.vt_layout_label') }}</th>
                        <th class="whitespace-nowrap px-5 py-4">{{ __('BusManagement::app.vt_capacity_label') }}</th>
                        <th class="whitespace-nowrap px-5 py-4">{{ __('BusManagement::app.vt_bus_count_label') }}</th>
                        <th class="whitespace-nowrap px-5 py-4">{{ __('BusManagement::app.col_status') }}</th>
                        <th class="whitespace-nowrap px-5 py-4 text-center">{{ __('BusManagement::app.col_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($vehicleTypes as $type)
                        @php
                            $classLabels = ['seater' => __('BusManagement::app.vt_class_seater'), 'sleeper' => __('BusManagement::app.vt_class_sleeper'), 'limousine' => __('BusManagement::app.vt_class_limousine'), 'vip_cabin' => __('BusManagement::app.vt_class_vip_cabin')];
                            $classColors = ['seater' => 'bg-sky-50 text-sky-700', 'sleeper' => 'bg-indigo-50 text-indigo-700', 'limousine' => 'bg-purple-50 text-purple-700', 'vip_cabin' => 'bg-amber-50 text-amber-700'];
                            $unitMap = ['seater' => __('BusManagement::app.vt_unit_seater'), 'sleeper' => __('BusManagement::app.vt_unit_sleeper'), 'limousine' => __('BusManagement::app.vt_unit_limousine'), 'vip_cabin' => __('BusManagement::app.vt_unit_vip_cabin')];
                            $busCount = $type->bus_count ?? 0;
                            $canDelete = $busCount === 0;
                            $floors = $type->floor_count ?? 1;
                        @endphp
                        <tr class="transition-colors duration-100 hover:bg-futa-orange-soft/30">
                            <td class="px-5 py-4 text-slate-500">{{ $vehicleTypes->firstItem() + $loop->index }}</td>
                            <td class="px-5 py-4">
                                <p class="font-bold text-slate-900">{{ $type->name }}</p>
                                @if($type->description)
                                    <p class="mt-0.5 max-w-xs truncate text-xs text-slate-400">{{ $type->description }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if($type->vehicle_class)
                                    <span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-bold {{ $classColors[$type->vehicle_class] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ $classLabels[$type->vehicle_class] ?? $type->vehicle_class }}
                                    </span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-slate-700">
                                @if($type->seat_rows && $type->seat_columns)
                                    {{ $type->seat_rows }} × {{ $type->seat_columns }} × {{ $floors }}
                                    @php $unused = $type->unused_seats ?? 0; @endphp
                                    @if($unused > 0)
                                        <span class="text-xs text-slate-400">(−{{ $unused }})</span>
                                    @endif
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 font-semibold text-slate-900">
                                {{ $type->default_capacity }}
                                <span class="text-xs font-normal text-slate-500">{{ $unitMap[$type->vehicle_class] ?? __('BusManagement::app.vt_unit_default') }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-bold {{ $busCount > 0 ? 'bg-sky-50 text-sky-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $busCount }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                @if($type->status === 'active')
                                    <span class="inline-flex whitespace-nowrap rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">{{ __('BusManagement::app.status_active') }}</span>
                                @else
                                    <span class="inline-flex whitespace-nowrap rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">{{ __('BusManagement::app.status_inactive') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    {{-- Sửa --}}
                                    <button id="btn-edit-{{ $type->id }}"
                                        @click="openEdit({{ json_encode([
                                            'id' => $type->id, 'name' => $type->name, 'description' => $type->description,
                                            'vehicle_class' => $type->vehicle_class, 'floor_count' => $type->floor_count ?? 1,
                                            'seat_rows' => $type->seat_rows, 'seat_columns' => $type->seat_columns,
                                            'unused_seats' => $type->unused_seats ?? 0,
                                            'default_capacity' => $type->default_capacity, 'status' => $type->status,
                                            'bus_count' => $busCount,
                                        ]) }})"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition hover:border-futa-orange hover:text-futa-orange">
                                        <x-heroicon-o-pencil-square class="size-3.5" /> {{ __('BusManagement::app.btn_edit') }}
                                    </button>

                                    {{-- Ngừng / Kích hoạt --}}
                                    <form method="POST" action="{{ route('bus-management.vehicle-types.toggle', $type->id) }}" class="inline">
                                        @csrf @method('PATCH')
                                        @if($type->status === 'active')
                                            <button type="submit" class="inline-flex whitespace-nowrap items-center gap-1.5 rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 transition hover:bg-amber-100">
                                            <x-heroicon-o-pause-circle class="size-3.5" /> {{ __('BusManagement::app.btn_deactivate') }}
                                            </button>
                                        @else
                                            <button type="submit" class="inline-flex whitespace-nowrap items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100">
                                            <x-heroicon-o-play-circle class="size-3.5" /> {{ __('BusManagement::app.btn_activate') }}
                                            </button>
                                        @endif
                                    </form>

                                    {{-- Xóa --}}
                                    @if($canDelete)
                                        <button id="btn-delete-{{ $type->id }}"
                                            @click="openDelete({{ json_encode(['id' => $type->id, 'name' => $type->name]) }})"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-rose-600 shadow-sm transition hover:border-rose-300 hover:bg-rose-50">
                                            <x-heroicon-o-trash class="size-3.5" /> {{ __('BusManagement::app.btn_delete') }}
                                        </button>
                                    @else
                                        <span title="{{ __('BusManagement::app.vt_type_in_use_tooltip', ['count' => $busCount]) }}"
                                            class="inline-flex cursor-not-allowed items-center gap-1.5 rounded-lg border border-slate-100 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-300">
                                            <x-heroicon-o-trash class="size-3.5" /> {{ __('BusManagement::app.btn_delete') }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center">
                                <x-core::empty-state class="mt-3 text-sm font-semibold text-slate-500" :title="__('BusManagement::app.empty')" compact />
                                <button @click="openAdd()" class="mt-3 text-sm font-medium text-futa-orange hover:underline">
                                    {{ __('BusManagement::app.btn_add_now') }}
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-4"><x-core::data-pagination :paginator="$vehicleTypes" /></div>
    </div>

    {{-- ══════════════════ ADD MODAL ══════════════════ --}}
    <div role="dialog" aria-modal="true"
        x-show="showAdd"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm"
        @click.self="showAdd = false" style="display:none">
        <div x-show="showAdd"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            class="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-2xl bg-white shadow-2xl">

            <div class="flex items-center justify-between bg-futa-orange px-5 py-3">
                <div class="flex items-center gap-2">
                    <span class="rounded-lg bg-white/20 p-1.5"><x-heroicon-o-plus class="size-4 text-white" /></span>
                    <h2 class="text-base font-bold text-white">{{ __('BusManagement::app.modal_add_title') }}</h2>
                </div>
                <button @click="showAdd = false" class="text-white/70 transition hover:text-white">
                    <x-heroicon-o-x-mark class="size-4" />
                </button>
            </div>

            <form id="form-add-vehicle-type" method="POST" action="{{ route('bus-management.vehicle-types.store') }}" class="space-y-4 p-5">
                @csrf

                {{-- Tên --}}
                <div>
                    <label for="add-name" class="mb-1 block text-xs font-semibold text-slate-700">
                        {{ __('BusManagement::app.field_name') }} <span class="text-rose-500">*</span>
                    </label>
                    <input id="add-name" type="text" name="name" required minlength="5" maxlength="100"
                        placeholder="{{ __('BusManagement::app.field_name_placeholder') }}"
                        class="{{ $inp }}" />
                    <p class="mt-0.5 text-xs text-slate-400">{{ __('BusManagement::app.field_name_hint') }}</p>
                </div>

                {{-- Phân loại dòng xe + Số tầng --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="add-class" class="mb-1 block text-xs font-semibold text-slate-700">
                            {{ __('BusManagement::app.field_vehicle_class') }} <span class="text-rose-500">*</span>
                        </label>
                        <select id="add-class" name="vehicle_class" required
                            x-model="addForm.vehicle_class"
                            @change="onClassChange(addForm)"
                            class="{{ $inp }}">
                            <option value="">{{ __('BusManagement::app.field_vehicle_class_select') }}</option>
                            @foreach($classes as $key => $label)
                                <option value="{{ $key }}">{{ __('BusManagement::app.vt_class_'.$key) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="add-floor" class="mb-1 block text-xs font-semibold text-slate-700">
                            {{ __('BusManagement::app.field_floor_count') }} <span class="text-rose-500">*</span>
                        </label>
                        <select id="add-floor" name="floor_count" required
                            x-model.number="addForm.floor_count"
                            @change="calcCapacity(addForm)"
                            class="{{ $inp }}">
                            <option value="1">{{ __('BusManagement::app.vt_floor_one') }}</option>
                            <option value="2" :disabled="maxFloors(addForm.vehicle_class) < 2">{{ __('BusManagement::app.vt_floor_two') }}</option>
                        </select>
                        <p class="mt-0.5 text-xs text-slate-400" x-show="maxFloors(addForm.vehicle_class) < 2">{{ __('BusManagement::app.vt_one_floor_only') }}</p>
                    </div>
                </div>

                {{-- Số hàng + Số cột --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="add-rows" class="mb-1 block text-xs font-semibold text-slate-700">
                            {{ __('BusManagement::app.field_seat_rows') }}
                        </label>
                        <input id="add-rows" type="number" name="seat_rows" min="1" max="30"
                            x-model.number="addForm.seat_rows"
                            @input="calcCapacity(addForm)"
                            class="{{ $inp }}" placeholder="{{ __('BusManagement::app.vt_rows_example') }}" />
                    </div>
                    <div>
                        <label for="add-cols" class="mb-1 block text-xs font-semibold text-slate-700">
                            {{ __('BusManagement::app.field_seat_columns') }}
                        </label>
                        <input id="add-cols" type="number" name="seat_columns" min="1" max="10"
                            x-model.number="addForm.seat_columns"
                            @input="calcCapacity(addForm)"
                            class="{{ $inp }}" placeholder="{{ __('BusManagement::app.vt_columns_example') }}" />
                    </div>
                </div>

                {{-- Số vị trí không dùng --}}
                <div>
                    <label for="add-unused" class="mb-1 block text-xs font-semibold text-slate-700">
                        Số vị trí không dùng <span class="text-slate-400 font-normal">(lối đi, ghế tài xế…)</span>
                    </label>
                        <input id="add-unused" type="number" name="unused_seats" min="0" max="255"
                        x-model.number="addForm.unused_seats"
                        @input="calcCapacity(addForm)"
                        class="{{ $inp }}" placeholder="0" />
                </div>

                {{-- Sức chứa (readonly, tự tính) --}}
                <div>
                    <label for="add-capacity" class="mb-1 block text-xs font-semibold text-slate-700">
                        {{ __('BusManagement::app.field_capacity') }} <span class="text-rose-500">*</span>
                        <span class="ml-1 text-xs font-normal text-slate-400">({{ __('BusManagement::app.vt_capacity_auto') }})</span>
                    </label>
                    <div class="relative">
                        <input id="add-capacity" type="number" name="default_capacity" required readonly
                            x-model.number="addForm.capacity"
                            class="{{ $inpDisabled }} pr-16" />
                        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs font-medium text-slate-400"
                            x-text="capacityUnit(addForm.vehicle_class)"></span>
                    </div>
                    <p class="mt-0.5 text-xs" :class="capacityError(addForm) ? 'text-rose-500 font-semibold' : 'text-slate-400'"
                        x-text="capacityError(addForm) || capacityHint(addForm.vehicle_class)"></p>
                </div>

                {{-- Mô tả --}}
                <div>
                    <label for="add-desc" class="mb-1 block text-xs font-semibold text-slate-700">
                        {{ __('BusManagement::app.field_description') }}
                    </label>
                    <textarea id="add-desc" name="description" rows="2" maxlength="500"
                        placeholder="{{ __('BusManagement::app.field_description_placeholder') }}"
                        class="{{ $inp }} resize-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                    <button type="button" @click="showAdd = false"
                        class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                        {{ __('BusManagement::app.btn_cancel') }}
                    </button>
                    <button type="submit" :disabled="!!capacityError(addForm)"
                        class="rounded-lg bg-futa-orange px-5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-futa-orange-dark disabled:cursor-not-allowed disabled:opacity-50">
                        {{ __('BusManagement::app.btn_store') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══════════════════ EDIT MODAL ══════════════════ --}}
    <div role="dialog" aria-modal="true"
        x-show="showEdit"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm"
        @click.self="showEdit = false" style="display:none">
        <div x-show="showEdit"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            class="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-2xl bg-white shadow-2xl">

            <div class="flex items-center justify-between bg-slate-800 px-5 py-3">
                <div class="flex items-center gap-2">
                    <span class="rounded-lg bg-white/20 p-1.5"><x-heroicon-o-pencil-square class="size-4 text-white" /></span>
                    <h2 class="text-base font-bold text-white">{{ __('BusManagement::app.modal_edit_title') }}</h2>
                </div>
                <button @click="showEdit = false" class="text-white/70 transition hover:text-white">
                    <x-heroicon-o-x-mark class="size-4" />
                </button>
            </div>

            <form id="form-edit-vehicle-type" :action="editAction" method="POST" class="space-y-4 p-5">
                @csrf @method('PUT')

                {{-- Khóa cứng nếu đã có xe --}}
                <template x-if="editData.bus_count > 0">
                    <div class="flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-semibold text-amber-700">
                        <x-heroicon-o-lock-closed class="size-4 shrink-0" />
                        {{ __('BusManagement::app.field_locked_hint') }}
                    </div>
                </template>

                {{-- Tên --}}
                <div>
                    <label for="edit-name" class="mb-1 block text-xs font-semibold text-slate-700">
                        Tên loại phương tiện <span class="text-rose-500">*</span>
                    </label>
                    <input id="edit-name" type="text" name="name" :value="editData.name" required minlength="5" maxlength="100"
                        class="{{ $inp }}" />
                </div>

                {{-- Phân loại dòng xe + Số tầng (disabled if in use) --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('BusManagement::app.vt_class_label') }} <span class="text-rose-500">*</span></label>
                        <select name="vehicle_class"
                            :disabled="editData.bus_count > 0"
                            :class="editData.bus_count > 0 ? '{{ $inpDisabled }}' : '{{ $inp }}'">
                            @foreach($classes as $key => $label)
                                <option value="{{ $key }}" :selected="editData.vehicle_class === '{{ $key }}'">{{ __('BusManagement::app.vt_class_'.$key) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('BusManagement::app.vt_floor_count') }} <span class="text-rose-500">*</span></label>
                        <select name="floor_count"
                            :disabled="editData.bus_count > 0"
                            x-model.number="editData.floor_count"
                            @change="calcCapacity(editData)"
                            :class="editData.bus_count > 0 ? '{{ $inpDisabled }}' : '{{ $inp }}'">
                            <option value="1" :selected="editData.floor_count == 1">{{ __('BusManagement::app.vt_floor_one') }}</option>
                            <option value="2" :selected="editData.floor_count == 2"
                                :disabled="maxFloors(editData.vehicle_class) < 2">{{ __('BusManagement::app.vt_floor_two') }}</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('BusManagement::app.vt_seat_rows') }}</label>
                        <input type="number" name="seat_rows" :value="editData.seat_rows" min="1" max="30"
                            x-model.number="editData.seat_rows"
                            @input="calcCapacity(editData)"
                            :disabled="editData.bus_count > 0"
                            :class="editData.bus_count > 0 ? '{{ $inpDisabled }}' : '{{ $inp }}'" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('BusManagement::app.vt_seat_columns') }}</label>
                        <input type="number" name="seat_columns" :value="editData.seat_columns" min="1" max="10"
                            x-model.number="editData.seat_columns"
                            @input="calcCapacity(editData)"
                            :disabled="editData.bus_count > 0"
                            :class="editData.bus_count > 0 ? '{{ $inpDisabled }}' : '{{ $inp }}'" />
                    </div>
                </div>

                {{-- Số vị trí không dùng --}}
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">
                        Số vị trí không dùng <span class="text-slate-400 font-normal">(lối đi, ghế tài xế…)</span>
                    </label>
                    <input type="number" name="unused_seats" min="0" max="255"
                        x-model.number="editData.unused_seats"
                        @input="calcCapacity(editData)"
                        :disabled="editData.bus_count > 0"
                        :class="editData.bus_count > 0 ? '{{ $inpDisabled }}' : '{{ $inp }}'" />
                </div>

                {{-- Sức chứa readonly --}}
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">
                        {{ __('BusManagement::app.field_capacity') }} <span class="text-rose-500">*</span>
                        <span class="ml-1 text-xs font-normal text-slate-400">({{ __('BusManagement::app.vt_capacity_auto') }})</span>
                    </label>
                    <input type="number" name="default_capacity" :value="editData.default_capacity" required readonly
                        x-model.number="editData.default_capacity"
                        :class="editData.bus_count > 0 ? '{{ $inpDisabled }}' : '{{ $inpDisabled }}'" />
                    <p class="mt-0.5 text-xs" :class="capacityError(editData) ? 'text-rose-500 font-semibold' : 'text-slate-400'"
                        x-text="capacityError(editData) || capacityHint(editData.vehicle_class)"></p>
                </div>

                <div>
                    <label for="edit-desc" class="mb-1 block text-xs font-semibold text-slate-700">{{ __('BusManagement::app.field_description') }}</label>
                    <textarea id="edit-desc" name="description" rows="2" maxlength="500"
                        x-text="editData.description"
                        class="{{ $inp }} resize-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                    <button type="button" @click="showEdit = false"
                        class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                        {{ __('BusManagement::app.btn_cancel') }}
                    </button>
                    <button type="submit"
                        class="rounded-lg bg-slate-800 px-5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-slate-900">
                        {{ __('BusManagement::app.btn_save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══════════════════ DELETE MODAL ══════════════════ --}}
    <div role="dialog" aria-modal="true"
        x-show="showDelete"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm"
        @click.self="showDelete = false" style="display:none">
        <div x-show="showDelete"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="p-6 text-center">
                <div class="mx-auto mb-4 grid size-14 place-items-center rounded-full bg-rose-100">
                    <x-heroicon-o-exclamation-triangle class="size-7 text-rose-500" />
                </div>
                <h3 class="text-lg font-bold text-slate-900">{{ __('BusManagement::app.modal_delete_title') }}</h3>
                <p class="mt-2 text-sm text-slate-500">
                    {{ __('BusManagement::app.modal_delete_message') }}
                    <span class="font-bold text-slate-800" x-text='"«" + deleteData.name + "»"'></span>?
                </p>
                <form id="form-delete-vehicle-type" :action="deleteAction" method="POST" class="mt-5 flex items-center justify-end gap-2">
                    @csrf @method('DELETE')
                    <button type="button" @click="showDelete = false"
                        class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                        {{ __('BusManagement::app.btn_cancel') }}
                    </button>
                    <button type="submit"
                        class="rounded-lg bg-rose-600 px-5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-rose-700">
                        {{ __('BusManagement::app.btn_confirm_delete') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
function vehicleTypeManager() {
    const capacityRanges = {
        seater:    { min: 8,  max: 50,  suggested: 40, unit: @js(__('BusManagement::app.vt_unit_seater')), maxFloors: 1 },
        sleeper:   { min: 20, max: 46,  suggested: 40, unit: @js(__('BusManagement::app.vt_unit_sleeper')), maxFloors: 2 },
        limousine: { min: 8,  max: 22,  suggested: 22, unit: @js(__('BusManagement::app.vt_unit_limousine')), maxFloors: 1 },
        vip_cabin: { min: 20, max: 24, suggested: 20, unit: @js(__('BusManagement::app.vt_unit_vip_cabin')), maxFloors: 2 },
    };
    const hintTemplate = @js(__('BusManagement::app.vt_capacity_hint'));
    const errorTemplate = @js(__('BusManagement::app.vt_capacity_invalid'));
    const rowsColumnsError = @js(__('BusManagement::app.vt_err_rows_columns'));
    const unusedSeatsError = @js(__('BusManagement::app.vt_err_unused_seats'));

    return {
        showAdd: false, showEdit: false, showDelete: false,
        editData: {}, editAction: '',
        deleteData: {}, deleteAction: '',
        addForm: { vehicle_class: '', floor_count: 1, seat_rows: null, seat_columns: null, unused_seats: 0, capacity: 0 },
        capacityTimer: null,

        maxFloors(cls) {
            return (capacityRanges[cls] || {}).maxFloors || 2;
        },

        capacityUnit(cls) { return (capacityRanges[cls] || {}).unit || ''; },

        capacityHint(cls) {
            const r = capacityRanges[cls];
            return r ? hintTemplate.replace(':min', r.min).replace(':max', r.max).replace(':unit', r.unit) : '';
        },

        /** Tính lại sức chứa cho form (add hoặc edit) */
        calcCapacity(form) {
            const rows    = parseInt(form.seat_rows)    || 0;
            const cols    = parseInt(form.seat_columns) || 0;
            const floors  = parseInt(form.floor_count)  || 1;
            const unused  = parseInt(form.unused_seats) || 0;
            const total   = rows * cols * floors - unused;
            form.capacity       = total > 0 ? total : 0;
            form.default_capacity = form.capacity; // keep in sync for editData
            clearTimeout(this.capacityTimer);
            const message = this.capacityError(form);
            if (message) {
                this.capacityTimer = setTimeout(() => window.FutaNotify?.warning(message, { key: 'vehicle-type-capacity' }), 350);
            } else {
                window.FutaNotify?.close('vehicle-type-capacity');
            }
        },

        capacityError(form) {
            const cap = parseInt(form.capacity) || 0;
            const r   = capacityRanges[form.vehicle_class];
            if (!r) return '';
            const rows = parseInt(form.seat_rows) || 0;
            const cols = parseInt(form.seat_columns) || 0;
            const floors = parseInt(form.floor_count) || 1;
            const unused = parseInt(form.unused_seats) || 0;
            if (unused > 0 && (rows === 0 || cols === 0)) return rowsColumnsError;
            if ((rows > 0) !== (cols > 0)) return rowsColumnsError;
            if (rows > 0 && cols > 0 && unused >= rows * cols * floors) {
                return unusedSeatsError.replace(':total', rows * cols * floors);
            }
            if (cap === 0) return '';
            if (cap < r.min || cap > r.max) return errorTemplate.replace(':min', r.min).replace(':max', r.max).replace(':unit', r.unit);
            return '';
        },

        onClassChange(form) {
            // Reset floor_count khi đổi dòng xe (tránh giữ 2 tầng cho seater)
            if (this.maxFloors(form.vehicle_class) < 2) {
                form.floor_count = 1;
            }
            const suggestion = capacityRanges[form.vehicle_class]?.suggested ?? 0;
            if (parseInt(form.seat_rows) > 0 && parseInt(form.seat_columns) > 0) {
                this.calcCapacity(form);
            } else {
                form.capacity = suggestion;
                form.default_capacity = suggestion;
            }
        },

        openAdd() {
            this.addForm = { vehicle_class: '', floor_count: 1, seat_rows: null, seat_columns: null, unused_seats: 0, capacity: 0 };
            this.showAdd = true;
        },
        openEdit(data) {
            this.editData   = { ...data, unused_seats: data.unused_seats ?? 0 };
            this.editAction = `{{ url('quan-tri/phuong-tien/loai-phuong-tien') }}/${data.id}`;
            this.showEdit   = true;
        },
        openDelete(data) {
            this.deleteData   = data;
            this.deleteAction = `{{ url('quan-tri/phuong-tien/loai-phuong-tien') }}/${data.id}`;
            this.showDelete   = true;
        },
    };
}
</script>
@include('BusManagement::partials.notifications')
@endsection
