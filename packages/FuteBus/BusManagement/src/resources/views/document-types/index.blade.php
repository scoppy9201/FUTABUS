@extends('Dashboard::layouts.admin')

@section('title', __('BusManagement::app.dt_title'))

@section('content')
@php $inp = 'w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20'; @endphp
<div x-data="docTypeManager()">

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-futa-orange">{{ $company?->name ?? 'FUTA Bus Lines' }}</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">{{ __('BusManagement::app.dt_title') }}</h1>
            <p class="mt-2 max-w-3xl text-sm font-medium text-slate-500">{{ __('BusManagement::app.dt_subtitle') }}</p>
        </div>
        <span class="rounded-full bg-futa-orange-soft px-4 py-2 text-sm font-bold text-futa-orange">
            {{ number_format($types->total()) }} {{ __('Dashboard::app.records') }}
        </span>
    </div>

    <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" action="{{ route('bus-management.document-types.index') }}" class="flex items-center gap-2">
            <div class="relative">
                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('BusManagement::app.dt_search_ph') }}"
                    class="w-72 rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-4 text-sm shadow-sm focus:border-futa-orange focus:outline-none focus:ring-2 focus:ring-futa-orange/20" />
            </div>
            <button type="submit" class="rounded-xl bg-futa-orange px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-futa-orange-dark">
                {{ __('BusManagement::app.btn_search') }}
            </button>
            @if($search)
                <a href="{{ route('bus-management.document-types.index') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-500 hover:text-slate-700">
                    {{ __('BusManagement::app.btn_clear_filter') }}
                </a>
            @endif
        </form>
        <button @click="openAdd()" class="inline-flex items-center gap-2 rounded-xl bg-futa-orange px-5 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-futa-orange-dark hover:shadow-lg">
            <x-heroicon-o-plus class="size-4" /> {{ __('BusManagement::app.dt_btn_add') }}
        </button>
    </div>

    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[800px] text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="whitespace-nowrap px-5 py-4">{{ __('BusManagement::app.col_stt') }}</th>
                        <th class="whitespace-nowrap px-5 py-4">{{ __('BusManagement::app.dt_col_name') }}</th>
                        <th class="whitespace-nowrap px-5 py-4">{{ __('BusManagement::app.dt_col_fields') }}</th>
                        <th class="whitespace-nowrap px-5 py-4">{{ __('BusManagement::app.dt_f_required') }}</th>
                        <th class="whitespace-nowrap px-5 py-4">{{ __('BusManagement::app.dt_expiry_yes') }}</th>
                        <th class="whitespace-nowrap px-5 py-4">{{ __('BusManagement::app.dt_blocks_yes') }}</th>
                        <th class="whitespace-nowrap px-5 py-4">{{ __('BusManagement::app.dt_col_records') }}</th>
                        <th class="whitespace-nowrap px-5 py-4">{{ __('BusManagement::app.col_status') }}</th>
                        <th class="whitespace-nowrap px-5 py-4 text-center">{{ __('BusManagement::app.col_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($types as $t)
                        @php $fs = $fields[$t->id] ?? collect(); @endphp
                        <tr class="hover:bg-futa-orange-soft/30">
                            <td class="px-5 py-4 text-slate-500">{{ $types->firstItem() + $loop->index }}</td>
                            <td class="px-5 py-4 font-bold text-slate-900">
                                {{ $t->name }}
                                @if($t->description)<span class="block max-w-xs truncate text-xs font-medium text-slate-400">{{ $t->description }}</span>@endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex max-w-sm flex-wrap gap-1.5">
                                    @foreach($fs as $f)
                                        <span class="whitespace-nowrap rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">{{ Illuminate\Support\Facades\Lang::has('BusManagement::app.'.$f->label) ? __('BusManagement::app.'.$f->label) : $f->label }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                @if($t->is_required)
                                    <span class="inline-flex whitespace-nowrap rounded-full bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-700">{{ __('BusManagement::app.dt_required_yes') }}</span>
                                @else
                                    <span class="text-xs font-semibold text-slate-400">{{ __('BusManagement::app.dt_required_no') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if($t->has_expiry ?? true)
                                    <span class="inline-flex whitespace-nowrap rounded-full bg-sky-50 px-2.5 py-1 text-xs font-bold text-sky-700">{{ __('BusManagement::app.dt_expiry_yes') }}</span>
                                @else
                                    <span class="text-xs font-semibold text-slate-400">{{ __('BusManagement::app.dt_expiry_no') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if($t->blocks_dispatch ?? false)
                                    <span class="inline-flex whitespace-nowrap rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700">{{ __('BusManagement::app.dt_blocks_yes') }}</span>
                                @else
                                    <span class="text-xs font-semibold text-slate-400">{{ __('BusManagement::app.dt_blocks_no') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">{{ $t->record_count }}</td>
                            <td class="px-5 py-4">
                                @if($t->status === 'active')
                                    <span class="inline-flex whitespace-nowrap rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">{{ __('BusManagement::app.status_active') }}</span>
                                @else
                                    <span class="inline-flex whitespace-nowrap rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">{{ __('BusManagement::app.status_inactive') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if($t->status === 'active')
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="openEdit({{ json_encode([
                                        'id' => $t->id, 'name' => $t->name, 'description' => $t->description,
                                        'is_required' => (int) $t->is_required,
                                        'has_expiry' => (int) ($t->has_expiry ?? 1),
                                        'blocks_dispatch' => (int) ($t->blocks_dispatch ?? 0),
                                        'warning_days' => (int) ($t->warning_days ?? 15),
                                        'fields' => $fs->map(fn ($f) => ['id' => $f->id, 'label' => $f->label,
                                            'type' => $f->field_type, 'required' => (int) $f->is_required])->values(),
                                    ]) }})"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition hover:border-futa-orange hover:text-futa-orange">
                                        <x-heroicon-o-pencil-square class="size-3.5" /> {{ __('BusManagement::app.dt_btn_edit') }}
                                    </button>
                                    <button @click="openDelete({{ json_encode(['id' => $t->id, 'name' => $t->name]) }})"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-rose-600 shadow-sm transition hover:border-rose-300 hover:bg-rose-50">
                                        <x-heroicon-o-trash class="size-3.5" /> {{ __('BusManagement::app.dt_btn_delete') }}
                                    </button>
                                </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-16 text-center text-sm font-semibold text-slate-500">
                                <x-core::empty-state :title="__('BusManagement::app.dt_empty')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($types->hasPages())
            <div class="border-t border-slate-100 px-5 py-4">{{ $types->links() }}</div>
        @endif
    </div>

    {{-- ADD / EDIT MODAL --}}
    <div role="dialog" aria-modal="true" x-show="showForm" x-cloak @click.self="showForm = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm" style="display:none">
        <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between px-5 py-3"
                 :class="isEdit ? 'bg-slate-800' : 'bg-futa-orange'">
                <h2 class="text-base font-bold text-white"
                    x-text="isEdit ? @js(__('BusManagement::app.dt_modal_edit')) : @js(__('BusManagement::app.dt_modal_add'))"></h2>
                <button type="button" @click="showForm = false" class="text-white/70 hover:text-white"><x-heroicon-o-x-mark class="size-4" /></button>
            </div>

            <form :action="formAction" method="POST" class="space-y-4 p-5">
                @csrf
                <template x-if="isEdit"><input type="hidden" name="_method" value="PUT"></template>

                {{-- Tên + Bắt buộc --}}
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('BusManagement::app.dt_f_name') }} <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" x-model="form.name" required maxlength="100" class="{{ $inp }}" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('BusManagement::app.dt_f_required') }}</label>
                        <select name="is_required" x-model="form.is_required" class="{{ $inp }}">
                            <option value="0">{{ __('BusManagement::app.dt_required_no') }}</option>
                            <option value="1">{{ __('BusManagement::app.dt_required_yes') }}</option>
                        </select>
                    </div>
                </div>

                {{-- Mô tả --}}
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('BusManagement::app.dt_f_desc') }}</label>
                    <textarea name="description" x-model="form.description" rows="2" maxlength="500" class="{{ $inp }} resize-none"></textarea>
                </div>

                {{-- Có thời hạn + Chặn điều xe --}}
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('BusManagement::app.dt_expiry_yes') }} <span class="text-rose-500">*</span></label>
                        <div class="flex gap-4 mt-1">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="has_expiry" value="1" x-model="form.has_expiry" class="accent-futa-orange" />
                                <span class="text-sm text-slate-700">{{ __('BusManagement::app.dt_expiry_yes') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="has_expiry" value="0" x-model="form.has_expiry" class="accent-futa-orange" />
                                <span class="text-sm text-slate-700">{{ __('BusManagement::app.dt_expiry_no') }}</span>
                            </label>
                        </div>
                    </div>
                    <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('BusManagement::app.dt_blocks_dispatch_label') }} <span class="text-rose-500">*</span></label>
                        <div class="flex gap-4 mt-1">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="blocks_dispatch" value="1" x-model="form.blocks_dispatch" class="accent-futa-orange" />
                                <span class="text-sm text-slate-700">{{ __('BusManagement::app.dt_blocks_yes') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="blocks_dispatch" value="0" x-model="form.blocks_dispatch" class="accent-futa-orange" />
                                <span class="text-sm text-slate-700">{{ __('BusManagement::app.dt_blocks_no') }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Số ngày cảnh báo (chỉ hiện khi có thời hạn) --}}
                <div x-show="form.has_expiry == '1'" x-transition>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">
                        Số ngày cảnh báo trước hạn <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="warning_days" x-model.number="form.warning_days"
                           min="1" max="365" :required="form.has_expiry == '1'"
                           class="{{ $inp }} w-48" />
                    <p class="mt-0.5 text-xs text-slate-400">1–365 ngày (mặc định 15)</p>
                </div>

                {{-- Ghi chú --}}
                <p class="rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-500">
                    <x-heroicon-o-information-circle class="inline size-3.5 mr-1" />
                    {{ __('BusManagement::app.dt_dates_hint') }}
                </p>

                {{-- Trường thông tin động --}}
                <div class="rounded-xl border border-slate-200 p-3">
                    <div class="mb-2 flex items-center justify-between">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ __('BusManagement::app.dt_f_fields') }} <span class="text-rose-500">*</span></p>
                        <button type="button" @click="addField()" class="text-xs font-bold text-futa-orange hover:underline">{{ __('BusManagement::app.dt_btn_add_field') }}</button>
                    </div>
                    <div class="space-y-2">
                        <template x-for="(f, i) in form.fields" :key="i">
                            <div class="grid grid-cols-12 items-center gap-2">
                                <input type="hidden" :name="`fields[${i}][id]`" :value="f.id ?? ''">
                                <select :name="`fields[${i}][label]`" x-model="f.label" required class="{{ $inp }} col-span-5">
                                    <option value="">{{ __('BusManagement::app.dt_field_presets') }}</option>
                                    <template x-for="option in fieldOptions" :key="option.value"><option :value="option.value" x-text="option.label"></option></template>
                                    <option x-show="f.id && !fieldOptions.some(option => option.value === f.label)" :value="f.label" x-text="f.label"></option>
                                </select>
                                <select :name="`fields[${i}][type]`" x-model="f.type" :disabled="f.id !== null" class="{{ $inp }} col-span-3 disabled:bg-slate-100">
                                    <option value="text">{{ __('BusManagement::app.dt_type_text') }}</option>
                                    <option value="number">{{ __('BusManagement::app.dt_type_number') }}</option>
                                    <option value="date">{{ __('BusManagement::app.dt_type_date') }}</option>
                                </select>
                                <select :name="`fields[${i}][required]`" x-model="f.required" class="{{ $inp }} col-span-3">
                                    <option value="1">{{ __('BusManagement::app.dt_required_yes') }}</option>
                                    <option value="0">{{ __('BusManagement::app.dt_required_no') }}</option>
                                </select>
                                <button type="button" @click="removeField(i)" x-show="form.fields.length > 1" :aria-label="@js(__('BusManagement::app.dt_btn_delete_field'))" :title="@js(__('BusManagement::app.dt_btn_delete_field'))" class="col-span-1 text-rose-500 hover:text-rose-700">
                                    <x-heroicon-o-trash class="mx-auto size-4" />
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                    <button type="button" @click="showForm = false" class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">{{ __('BusManagement::app.btn_cancel') }}</button>
                    <button type="submit" class="rounded-lg bg-futa-orange px-5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-futa-orange-dark"
                            x-text="isEdit ? @js(__('BusManagement::app.btn_save')) : @js(__('BusManagement::app.btn_store'))"></button>
                </div>
            </form>
        </div>
    </div>

    {{-- DEACTIVATE MODAL --}}
    <div role="dialog" aria-modal="true" x-show="showDelete" x-cloak @click.self="showDelete = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm" style="display:none">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 text-center shadow-2xl">
            <div class="mx-auto mb-4 grid size-14 place-items-center rounded-full bg-rose-100">
                <x-heroicon-o-exclamation-triangle class="size-7 text-rose-500" />
            </div>
            <h3 class="text-lg font-bold text-slate-900">{{ __('BusManagement::app.dt_modal_delete') }}</h3>
            <p class="mt-2 text-sm text-slate-500">
                {{ __('BusManagement::app.dt_delete_msg') }}
                <span class="font-bold text-slate-800" x-text="delData.name"></span>?
            </p>
            <form :action="delAction" method="POST" class="mt-5 flex items-center justify-end gap-2">
                @csrf
                @method('DELETE')
                <button type="button" @click="showDelete = false" class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">{{ __('BusManagement::app.btn_cancel') }}</button>
                <button type="submit" class="rounded-lg bg-rose-600 px-5 py-2 text-xs font-bold text-white transition hover:bg-rose-700">{{ __('BusManagement::app.btn_confirm_delete') }}</button>
            </form>
        </div>
    </div>
</div>

<script>
function docTypeManager() {
    const fieldOptions = [
        { value: 'document_field_registration_number', label: @js(__('BusManagement::app.dt_field_registration_number')) },
        { value: 'document_field_owner', label: @js(__('BusManagement::app.dt_field_owner')) },
        { value: 'document_field_issuing_authority', label: @js(__('BusManagement::app.dt_field_issuing_authority')) },
        { value: 'document_field_vehicle_model', label: @js(__('BusManagement::app.dt_field_vehicle_model')) },
        { value: 'document_field_note', label: @js(__('BusManagement::app.dt_field_note')) },
    ];
    const newField = () => ({ id: null, label: '', type: 'text', required: '1' });
    const blank = () => ({ name: '', description: '', is_required: '0', has_expiry: '1', blocks_dispatch: '0', warning_days: 15, fields: [newField()] });
    const base = `{{ url('quan-tri/phuong-tien/loai-giay-to') }}`;
    return {
        showForm: false, showDelete: false, isEdit: false, fieldOptions,
        form: blank(), formAction: base, delData: {}, delAction: '',
        openAdd() { this.isEdit = false; this.form = blank(); this.formAction = base; this.showForm = true; },
        openEdit(d) {
            this.isEdit = true;
            this.form = {
                name: d.name, description: d.description ?? '', is_required: String(d.is_required),
                has_expiry: String(d.has_expiry ?? 1), blocks_dispatch: String(d.blocks_dispatch ?? 0),
                warning_days: d.warning_days ?? 15,
                fields: d.fields.map(f => ({ id: f.id, label: f.label, type: f.type, required: String(f.required) })),
            };
            this.formAction = `${base}/${d.id}`; this.showForm = true;
        },
        openDelete(d) { this.delData = d; this.delAction = `${base}/${d.id}`; this.showDelete = true; },
        addField() { this.form.fields.push(newField()); },
        removeField(i) { if (this.form.fields.length > 1) this.form.fields.splice(i, 1); },
    };
}
</script>
@include('BusManagement::partials.notifications')
@endsection
