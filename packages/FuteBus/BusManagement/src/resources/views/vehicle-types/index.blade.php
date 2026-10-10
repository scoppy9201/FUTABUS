@extends('Dashboard::layouts.admin')

@section('title', __('BusManagement::app.vehicle_types_title'))

@section('content')
<div x-data="vehicleTypeManager()">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-futa-orange">
                {{ $company?->name ?? 'FUTA Bus Lines' }}
            </p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">
                {{ __('BusManagement::app.vehicle_types_title') }}
            </h1>
            <p class="mt-2 max-w-3xl text-sm font-medium text-slate-500 sm:text-base">
                {{ __('BusManagement::app.vehicle_types_subtitle') }}
            </p>
        </div>
        <span class="rounded-full bg-futa-orange-soft px-4 py-2 text-sm font-bold text-futa-orange-dark">
            {{ number_format($vehicleTypes->total()) }} {{ __('Dashboard::app.records') }}
        </span>
    </div>

    {{-- ── Flash messages ───────────────────────────────────────── --}}
    @if(session('success'))
        <div class="mt-4 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            <x-heroicon-o-check-circle class="size-5 shrink-0" />
            {{ session('success') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="mt-4 flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-700">
            <x-heroicon-o-exclamation-triangle class="size-5 shrink-0" />
            {{ session('warning') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
            <ul class="list-inside list-disc space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ── Toolbar: Search + Add  --}}
    <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" action="{{ route('bus-management.vehicle-types.index') }}" class="flex items-center gap-2">
            <div class="relative">
                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                <input
                    id="search-vehicle-type"
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="{{ __('BusManagement::app.search_placeholder') }}"
                    class="w-72 rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-4 text-sm text-slate-700 placeholder-slate-400 shadow-sm transition focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20"
                />
            </div>
            <button type="submit" class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-200">
                {{ __('BusManagement::app.btn_search') }}
            </button>
            @if($search)
                <a href="{{ route('bus-management.vehicle-types.index') }}"
                   class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-500 transition hover:text-slate-700">
                    {{ __('BusManagement::app.btn_clear_filter') }}
                </a>
            @endif
        </form>

        <button
            id="btn-add-vehicle-type"
            @click="openAdd()"
            class="inline-flex items-center gap-2 rounded-xl bg-futa-orange px-5 py-2.5 text-sm font-bold text-white shadow-md transition-all duration-200 hover:opacity-90 hover:shadow-lg"
        >
            <x-heroicon-o-plus class="size-4" />
            {{ __('BusManagement::app.btn_add') }}
        </button>
    </div>

    {{-- ── Table  --}}
    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-160 text-left text-sm">
                <caption class="sr-only">{{ __('BusManagement::app.vehicle_types_title') }}</caption>
                <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th scope="col" class="whitespace-nowrap px-5 py-4">{{ __('BusManagement::app.col_stt') }}</th>
                        <th scope="col" class="whitespace-nowrap px-5 py-4">{{ __('BusManagement::app.col_name') }}</th>
                        <th scope="col" class="whitespace-nowrap px-5 py-4">{{ __('BusManagement::app.col_description') }}</th>
                        <th scope="col" class="whitespace-nowrap px-5 py-4">{{ __('BusManagement::app.col_capacity') }}</th>
                        <th scope="col" class="whitespace-nowrap px-5 py-4">{{ __('BusManagement::app.col_status') }}</th>
                        <th scope="col" class="whitespace-nowrap px-5 py-4 text-center">{{ __('BusManagement::app.col_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($vehicleTypes as $type)
                        <tr class="transition-colors duration-100 hover:bg-futa-orange-soft/30">
                            <td class="px-5 py-4 text-slate-500">{{ $vehicleTypes->firstItem() + $loop->index }}</td>
                            <td class="px-5 py-4 font-bold text-slate-900">{{ $type->name }}</td>
                            <td class="max-w-xs truncate px-5 py-4 text-slate-600" title="{{ $type->description }}">
                                {{ $type->description ?: '—' }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-slate-700">
                                {{ $type->default_capacity }} {{ __('BusManagement::app.field_capacity_unit') }}
                            </td>
                            <td class="px-5 py-4">
                                @if($type->status === 'active')
                                    <span class="inline-flex whitespace-nowrap rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">
                                        {{ __('BusManagement::app.status_active') }}
                                    </span>
                                @else
                                    <span class="inline-flex whitespace-nowrap rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">
                                        {{ __('BusManagement::app.status_inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button
                                        id="btn-edit-{{ $type->id }}"
                                        @click="openEdit({{ json_encode(['id' => $type->id, 'name' => $type->name, 'description' => $type->description, 'default_capacity' => $type->default_capacity, 'status' => $type->status]) }})"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition hover:border-futa-orange hover:bg-futa-orange-soft hover:text-futa-orange"
                                    >
                                        <x-heroicon-o-pencil-square class="size-3.5" />
                                        {{ __('BusManagement::app.btn_edit') }}
                                    </button>
                                    @if($type->status === 'active')
                                    <button
                                        id="btn-delete-{{ $type->id }}"
                                        @click="openDelete({{ json_encode(['id' => $type->id, 'name' => $type->name]) }})"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-rose-600 shadow-sm transition hover:border-rose-300 hover:bg-rose-50"
                                    >
                                        <x-heroicon-o-trash class="size-3.5" />
                                        {{ __('BusManagement::app.btn_delete') }}
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <span class="mx-auto grid size-14 place-items-center rounded-full bg-slate-50 text-slate-300">
                                    <x-heroicon-o-inbox class="size-8" />
                                </span>
                                <p class="mt-3 text-sm font-semibold text-slate-500">{{ __('BusManagement::app.empty') }}</p>
                                <button @click="openAdd()" class="mt-3 text-sm font-medium text-futa-orange hover:underline">
                                    {{ __('BusManagement::app.btn_add_now') }}
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($vehicleTypes->hasPages())
            <div class="border-t border-slate-100 px-5 py-4">{{ $vehicleTypes->links() }}</div>
        @endif
    </div>

    {{-- ADD MODAL--}}
    <div
        x-show="showAdd"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm"
        @click.self="showAdd = false"
        style="display:none"
    >
        <div
            x-show="showAdd"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl"
        >
            {{-- Modal Header --}}
            <div class="flex items-center justify-between bg-futa-orange px-5 py-3">
                <div class="flex items-center gap-2">
                    <span class="rounded-lg bg-white/20 p-1.5">
                        <x-heroicon-o-plus class="size-4 text-white" />
                    </span>
                    <h2 class="text-base font-bold text-white">{{ __('BusManagement::app.modal_add_title') }}</h2>
                </div>
                <button @click="showAdd = false" class="text-white/70 transition hover:text-white">
                    <x-heroicon-o-x-mark class="size-4" />
                </button>
            </div>

            {{-- Modal Body --}}
            <form id="form-add-vehicle-type" method="POST" action="{{ route('bus-management.vehicle-types.store') }}" class="space-y-3 p-5">
                @csrf
                <div>
                    <label for="add-name" class="mb-1 block text-xs font-semibold text-slate-700">
                        {{ __('BusManagement::app.field_name') }}
                        <span class="text-rose-500">{{ __('BusManagement::app.field_required') }}</span>
                    </label>
                    <input
                        id="add-name"
                        type="text"
                        name="name"
                        required
                        maxlength="100"
                        placeholder="{{ __('BusManagement::app.field_name_placeholder') }}"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-800 placeholder-slate-400 transition focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20"
                    />
                    <p class="mt-0.5 text-xs text-slate-400">{{ __('BusManagement::app.field_name_hint') }}</p>
                </div>

                <div>
                    <label for="add-description" class="mb-1 block text-xs font-semibold text-slate-700">
                        {{ __('BusManagement::app.field_description') }}
                    </label>
                    <textarea
                        id="add-description"
                        name="description"
                        rows="2"
                        maxlength="500"
                        placeholder="{{ __('BusManagement::app.field_description_placeholder') }}"
                        class="w-full resize-none rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-800 placeholder-slate-400 transition focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20"
                    ></textarea>
                </div>

                <div>
                    <label for="add-capacity" class="mb-1 block text-xs font-semibold text-slate-700">
                        {{ __('BusManagement::app.field_capacity') }}
                        <span class="text-rose-500">{{ __('BusManagement::app.field_required') }}</span>
                    </label>
                    <div class="relative">
                        <input
                            id="add-capacity"
                            type="number"
                            name="default_capacity"
                            min="1"
                            max="255"
                            value="45"
                            required
                            class="w-full rounded-lg border border-slate-200 py-2 pl-3 pr-14 text-sm text-slate-800 transition focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20"
                        />
                        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs font-medium text-slate-400">
                            {{ __('BusManagement::app.field_capacity_unit') }}
                        </span>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                    <button type="button" @click="showAdd = false"
                        class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                        {{ __('BusManagement::app.btn_cancel') }}
                    </button>
                    <button type="submit"
                        class="rounded-lg bg-futa-orange px-5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-futa-orange-dark">
                        {{ __('BusManagement::app.btn_store') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    {{-- EDIT MODAL --}}
    <div
        x-show="showEdit"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm"
        @click.self="showEdit = false"
        style="display:none"
    >
        <div
            x-show="showEdit"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl"
        >
            {{-- Modal Header --}}
            <div class="flex items-center justify-between bg-slate-800 px-5 py-3">
                <div class="flex items-center gap-2">
                    <span class="rounded-lg bg-white/20 p-1.5">
                        <x-heroicon-o-pencil-square class="size-4 text-white" />
                    </span>
                    <h2 class="text-base font-bold text-white">{{ __('BusManagement::app.modal_edit_title') }}</h2>
                </div>
                <button @click="showEdit = false" class="text-white/70 transition hover:text-white">
                    <x-heroicon-o-x-mark class="size-4" />
                </button>
            </div>

            {{-- Modal Body --}}
            <form id="form-edit-vehicle-type" :action="editAction" method="POST" class="space-y-3 p-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="edit-name" class="mb-1 block text-xs font-semibold text-slate-700">
                        {{ __('BusManagement::app.field_name') }}
                        <span class="text-rose-500">{{ __('BusManagement::app.field_required') }}</span>
                    </label>
                    <input
                        id="edit-name"
                        type="text"
                        name="name"
                        :value="editData.name"
                        required
                        maxlength="100"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-800 placeholder-slate-400 transition focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20"
                    />
                    <p class="mt-0.5 text-xs text-slate-400">{{ __('BusManagement::app.field_name_hint') }}</p>
                </div>

                <div>
                    <label for="edit-description" class="mb-1 block text-xs font-semibold text-slate-700">
                        {{ __('BusManagement::app.field_description') }}
                    </label>
                    <textarea
                        id="edit-description"
                        name="description"
                        rows="2"
                        maxlength="500"
                        x-text="editData.description"
                        class="w-full resize-none rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-800 placeholder-slate-400 transition focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20"
                    ></textarea>
                </div>

                <div>
                    <label for="edit-capacity" class="mb-1 block text-xs font-semibold text-slate-700">
                        {{ __('BusManagement::app.field_capacity') }}
                        <span class="text-rose-500">{{ __('BusManagement::app.field_required') }}</span>
                    </label>
                    <div class="relative">
                        <input
                            id="edit-capacity"
                            type="number"
                            name="default_capacity"
                            :value="editData.default_capacity"
                            min="1"
                            max="255"
                            required
                            class="w-full rounded-lg border border-slate-200 py-2 pl-3 pr-14 text-sm text-slate-800 transition focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20"
                        />
                        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs font-medium text-slate-400">
                            {{ __('BusManagement::app.field_capacity_unit') }}
                        </span>
                    </div>
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

    {{-- DELETE CONFIRM MODAL                                       --}}
    <div
        x-show="showDelete"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm"
        @click.self="showDelete = false"
        style="display:none"
    >
        <div
            x-show="showDelete"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl"
        >
            <div class="p-6 text-center">
                <div class="mx-auto mb-4 grid size-14 place-items-center rounded-full bg-rose-100">
                    <x-heroicon-o-exclamation-triangle class="size-7 text-rose-500" />
                </div>
                <h3 class="text-lg font-bold text-slate-900">
                    {{ __('BusManagement::app.modal_delete_title') }}
                </h3>
                <p class="mt-2 text-sm text-slate-500">
                    {{ __('BusManagement::app.modal_delete_message') }}
                    <span class="font-bold text-slate-800" x-text='"«" + deleteData.name + "»"'></span>?
                </p>

                <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-left text-xs text-amber-700">
                    <p class="mb-1 font-semibold">{{ __('BusManagement::app.modal_delete_note_title') }}</p>
                    <ul class="list-inside list-disc space-y-1">
                        <li>{{ __('BusManagement::app.modal_delete_note_1') }}</li>
                        <li>{{ __('BusManagement::app.modal_delete_note_2') }}</li>
                    </ul>
                </div>

                <form id="form-delete-vehicle-type" :action="deleteAction" method="POST" class="mt-5 flex items-center justify-end gap-2">
                    @csrf
                    @method('DELETE')
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
    return {
        showAdd:     false,
        showEdit:    false,
        showDelete:  false,
        editData:    {},
        editAction:  '',
        deleteData:  {},
        deleteAction:'',

        openAdd() {
            this.showAdd = true;
        },
        openEdit(data) {
            this.editData   = data;
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
@endsection
