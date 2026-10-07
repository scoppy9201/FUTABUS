@extends('Dashboard::layouts.admin')

@section('title', __('BusManagement::app.dt_title'))

@section('content')
@php $inp = 'w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#F26522] focus:outline-none focus:ring-2 focus:ring-[#F26522]/20'; @endphp
<div x-data="docTypeManager()">

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#ef5222]">{{ $company?->name ?? 'FUTA Bus Lines' }}</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">{{ __('BusManagement::app.dt_title') }}</h1>
            <p class="mt-2 max-w-3xl text-sm font-medium text-slate-500">{{ __('BusManagement::app.dt_subtitle') }}</p>
        </div>
        <span class="rounded-full bg-orange-50 px-4 py-2 text-sm font-bold text-[#d7461a]">
            {{ number_format($types->total()) }} {{ __('Dashboard::app.records') }}
        </span>
    </div>

    @if(session('success'))
        <div class="mt-4 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            <x-heroicon-o-check-circle class="size-5 shrink-0" /> {{ session('success') }}
        </div>
    @endif
    @if(session('warning'))
        <div class="mt-4 flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-700">
            <x-heroicon-o-exclamation-triangle class="size-5 shrink-0" /> {{ session('warning') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
            <ul class="list-inside list-disc space-y-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" action="{{ route('bus-management.document-types.index') }}" class="flex items-center gap-2">
            <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('BusManagement::app.dt_search_ph') }}"
                class="w-72 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-[#F26522] focus:outline-none focus:ring-2 focus:ring-[#F26522]/20" />
            <button type="submit" class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200">{{ __('BusManagement::app.btn_search') }}</button>
            @if($search)
                <a href="{{ route('bus-management.document-types.index') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-500">{{ __('BusManagement::app.btn_clear_filter') }}</a>
            @endif
        </form>
        <button @click="openAdd()" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[#F26522] to-[#E31B23] px-5 py-2.5 text-sm font-bold text-white shadow-md hover:opacity-90">
            <x-heroicon-o-plus class="size-4" /> {{ __('BusManagement::app.dt_btn_add') }}
        </button>
    </div>

    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-4">{{ __('BusManagement::app.col_stt') }}</th>
                        <th class="px-5 py-4">{{ __('BusManagement::app.dt_col_name') }}</th>
                        <th class="px-5 py-4">{{ __('BusManagement::app.dt_col_fields') }}</th>
                        <th class="px-5 py-4">{{ __('BusManagement::app.dt_col_required') }}</th>
                        <th class="px-5 py-4">{{ __('BusManagement::app.dt_col_records') }}</th>
                        <th class="px-5 py-4">{{ __('BusManagement::app.col_status') }}</th>
                        <th class="px-5 py-4 text-center">{{ __('BusManagement::app.col_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($types as $t)
                        @php $fs = $fields[$t->id] ?? collect(); @endphp
                        <tr class="hover:bg-orange-50/30">
                            <td class="px-5 py-4 text-slate-500">{{ $types->firstItem() + $loop->index }}</td>
                            <td class="px-5 py-4 font-bold text-slate-900">
                                {{ $t->name }}
                                @if($t->description)<span class="block max-w-xs truncate text-xs font-medium text-slate-400">{{ $t->description }}</span>@endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex max-w-sm flex-wrap gap-1.5">
                                    @foreach($fs as $f)
                                        <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">{{ $f->label }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                @if($t->is_required)
                                    <span class="rounded-full bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-700">{{ __('BusManagement::app.dt_required_yes') }}</span>
                                @else
                                    <span class="text-xs font-semibold text-slate-400">{{ __('BusManagement::app.dt_required_no') }}</span>
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
                                        'fields' => $fs->map(fn ($f) => ['id' => $f->id, 'label' => $f->label,
                                            'type' => $f->field_type, 'required' => (int) $f->is_required])->values(),
                                    ]) }})" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-[#F26522] hover:text-[#F26522]">
                                        <x-heroicon-o-pencil-square class="size-3.5" /> {{ __('BusManagement::app.dt_btn_edit') }}
                                    </button>
                                    <button @click="openDelete({{ json_encode(['id' => $t->id, 'name' => $t->name]) }})"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-rose-600 hover:border-rose-300 hover:bg-rose-50">
                                        <x-heroicon-o-no-symbol class="size-3.5" /> {{ __('BusManagement::app.dt_btn_deactivate') }}
                                    </button>
                                </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-16 text-center text-sm font-semibold text-slate-500">{{ __('BusManagement::app.dt_empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($types->hasPages())
            <div class="border-t border-slate-100 px-5 py-4">{{ $types->links() }}</div>
        @endif
    </div>

    {{-- ADD / EDIT MODAL --}}
    <div x-show="showForm" x-cloak @click.self="showForm = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm" style="display:none">
        <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between px-5 py-3"
                 :class="isEdit ? 'bg-slate-800' : 'bg-gradient-to-r from-[#F26522] to-[#E31B23]'">
                <h2 class="text-base font-bold text-white"
                    x-text="isEdit ? @js(__('BusManagement::app.dt_modal_edit')) : @js(__('BusManagement::app.dt_modal_add'))"></h2>
                <button type="button" @click="showForm = false" class="text-white/70 hover:text-white"><x-heroicon-o-x-mark class="size-4" /></button>
            </div>

            <form :action="formAction" method="POST" class="space-y-3 p-5">
                @csrf
                <template x-if="isEdit"><input type="hidden" name="_method" value="PUT"></template>

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

                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">{{ __('BusManagement::app.dt_f_desc') }}</label>
                    <textarea name="description" x-model="form.description" rows="2" maxlength="500" class="{{ $inp }} resize-none"></textarea>
                </div>

                <div class="rounded-xl border border-slate-200 p-3">
                    <div class="mb-2 flex items-center justify-between">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ __('BusManagement::app.dt_f_fields') }} <span class="text-rose-500">*</span></p>
                        <button type="button" @click="addField()" class="text-xs font-bold text-[#F26522] hover:underline">{{ __('BusManagement::app.dt_btn_add_field') }}</button>
                    </div>
                    <div class="space-y-2">
                        <template x-for="(f, i) in form.fields" :key="i">
                            <div class="grid grid-cols-12 items-center gap-2">
                                <input type="hidden" :name="`fields[${i}][id]`" :value="f.id ?? ''">
                                <input type="text" :name="`fields[${i}][label]`" x-model="f.label" required maxlength="100"
                                       placeholder="{{ __('BusManagement::app.dt_f_label') }}" class="{{ $inp }} col-span-5" />
                                <select :name="`fields[${i}][type]`" x-model="f.type" :disabled="f.id !== null" class="{{ $inp }} col-span-3 disabled:bg-slate-100">
                                    <option value="text">{{ __('BusManagement::app.dt_type_text') }}</option>
                                    <option value="number">{{ __('BusManagement::app.dt_type_number') }}</option>
                                    <option value="date">{{ __('BusManagement::app.dt_type_date') }}</option>
                                </select>
                                <select :name="`fields[${i}][required]`" x-model="f.required" class="{{ $inp }} col-span-3">
                                    <option value="1">{{ __('BusManagement::app.dt_required_yes') }}</option>
                                    <option value="0">{{ __('BusManagement::app.dt_required_no') }}</option>
                                </select>
                                <button type="button" @click="removeField(i)" x-show="form.fields.length > 1" class="col-span-1 text-rose-500 hover:text-rose-700">
                                    <x-heroicon-o-trash class="mx-auto size-4" />
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                    <button type="button" @click="showForm = false" class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">{{ __('BusManagement::app.btn_cancel') }}</button>
                    <button type="submit" class="rounded-lg bg-[#F26522] px-5 py-2 text-xs font-bold text-white shadow-sm hover:bg-[#d9561d]"
                            x-text="isEdit ? @js(__('BusManagement::app.btn_save')) : @js(__('BusManagement::app.btn_store'))"></button>
                </div>
            </form>
        </div>
    </div>

    {{-- DEACTIVATE MODAL --}}
    <div x-show="showDelete" x-cloak @click.self="showDelete = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm" style="display:none">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 text-center shadow-2xl">
            <div class="mx-auto mb-4 grid size-14 place-items-center rounded-full bg-rose-100">
                <x-heroicon-o-exclamation-triangle class="size-7 text-rose-500" />
            </div>
            <h3 class="text-lg font-bold text-slate-900">{{ __('BusManagement::app.dt_modal_deactivate') }}</h3>
            <p class="mt-2 text-sm text-slate-500">
                {{ __('BusManagement::app.dt_deactivate_msg') }}
                <span class="font-bold text-slate-800" x-text="delData.name"></span>?
            </p>
            <form :action="delAction" method="POST" class="mt-5 flex items-center justify-end gap-2">
                @csrf
                @method('DELETE')
                <button type="button" @click="showDelete = false" class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">{{ __('BusManagement::app.btn_cancel') }}</button>
                <button type="submit" class="rounded-lg bg-rose-600 px-5 py-2 text-xs font-bold text-white hover:bg-rose-700">{{ __('BusManagement::app.btn_confirm_delete') }}</button>
            </form>
        </div>
    </div>
</div>

<script>
function docTypeManager() {
    const newField = () => ({ id: null, label: '', type: 'text', required: '1' });
    const blank = () => ({ name: '', description: '', is_required: '0', fields: [newField()] });
    const base = `{{ url('quan-tri/phuong-tien/loai-giay-to') }}`;
    return {
        showForm: false, showDelete: false, isEdit: false,
        form: blank(), formAction: base, delData: {}, delAction: '',
        openAdd() { this.isEdit = false; this.form = blank(); this.formAction = base; this.showForm = true; },
        openEdit(d) {
            this.isEdit = true;
            this.form = {
                name: d.name, description: d.description ?? '', is_required: String(d.is_required),
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
@endsection