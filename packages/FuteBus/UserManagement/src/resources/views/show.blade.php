@extends('Dashboard::layouts.admin')

@section('title', __('UserManagement::app.detail_title'))
@section('page_title', __('UserManagement::app.detail_title'))

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-orange-100 bg-gradient-to-r from-orange-50 to-rose-50 p-4 sm:px-5">
        <div class="flex items-center gap-3">
            <a href="{{ route('staff-accounts.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                <x-heroicon-o-arrow-left class="size-4" />{{ __('UserManagement::app.back') }}
            </a>
            <div class="flex items-center gap-2">
                <x-heroicon-o-user-circle class="size-6 text-[#F26522]" />
                <h1 class="text-lg font-bold text-slate-900 sm:text-xl">{{ __('UserManagement::app.detail_title') }}</h1>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('staff-accounts.edit', $staff->id) }}" class="inline-flex items-center gap-2 rounded-lg border border-orange-200 bg-white px-3 py-2 text-sm font-bold text-[#F26522] transition hover:bg-orange-50">
                <x-heroicon-o-pencil-square class="size-4" />{{ __('UserManagement::app.edit_action') }}
            </a>
            <form method="POST" action="{{ route('staff-accounts.destroy', $staff->id) }}" data-confirm data-confirm-title="{{ __('UserManagement::app.delete_title') }}" data-confirm-message="{{ __('UserManagement::app.delete_message', ['name' => $staff->name]) }}" data-confirm-label="{{ __('UserManagement::app.delete_action') }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-3 py-2 text-sm font-bold text-white transition hover:bg-red-700">
                    <x-heroicon-o-trash class="size-4" />{{ __('UserManagement::app.delete_action') }}
                </button>
            </form>
        </div>
    </div>

    <div class="space-y-3">
        <section class="flex items-center gap-4 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <div class="grid size-16 shrink-0 place-items-center overflow-hidden rounded-full border border-slate-200 bg-slate-50 text-xl font-bold text-slate-500">
                @if($staff->avatar)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($staff->avatar) }}" alt="{{ $staff->name }}" class="size-full object-cover">
                @else
                    {{ mb_strtoupper(mb_substr($staff->name, 0, 1)) }}
                @endif
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-900">{{ $staff->name }}</h2>
                <span @class(['mt-1 inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold', 'bg-emerald-50 text-emerald-700' => $staff->is_active, 'bg-amber-50 text-amber-700' => ! $staff->is_active])>
                    @if($staff->is_active)<x-heroicon-o-check-circle class="size-3.5" />@else<x-heroicon-o-x-circle class="size-3.5" />@endif
                    {{ __('UserManagement::app.'.($staff->is_active ? 'active' : 'inactive')) }}
                </span>
            </div>
        </section>

        <div class="grid items-start gap-3 xl:grid-cols-[1.2fr_1fr]">
        <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center gap-2 border-b border-slate-100 bg-slate-50 px-4 py-3">
                    <x-heroicon-o-user class="size-5 text-slate-600" />
                    <h3 class="text-sm font-bold text-slate-800">{{ __('UserManagement::app.basic_info') }}</h3>
                </div>
                <div class="grid gap-4 p-4 sm:grid-cols-[minmax(0,1fr)_9rem]">
                    <dl class="grid content-start grid-cols-[7rem_minmax(0,1fr)] gap-x-3 gap-y-3 text-sm">
                        <dt class="font-medium text-slate-500">{{ __('UserManagement::app.fields.name') }}</dt><dd class="font-semibold text-slate-900">{{ $staff->name }}</dd>
                        <dt class="font-medium text-slate-500">{{ __('UserManagement::app.fields.date_of_birth') }}</dt><dd class="font-semibold text-slate-900">{{ $staff->date_of_birth?->format('d/m/Y') ?: '—' }}</dd>
                        <dt class="font-medium text-slate-500">{{ __('UserManagement::app.fields.gender') }}</dt><dd class="font-semibold text-slate-900">{{ $staff->gender ? __('UserManagement::app.gender_values.'.$staff->gender) : '—' }}</dd>
                        <dt class="font-medium text-slate-500">{{ __('UserManagement::app.fields.phone') }}</dt><dd class="font-semibold text-slate-900">{{ $staff->phone ?: '—' }}</dd>
                        <dt class="font-medium text-slate-500">{{ __('UserManagement::app.fields.address') }}</dt><dd class="font-semibold text-slate-900">{{ $staff->address ?: '—' }}</dd>
                    </dl>
                    <div class="sm:border-l sm:border-slate-100 sm:pl-4">
                        <p class="text-xs font-medium text-slate-500">{{ __('UserManagement::app.avatar') }}</p>
                        <div class="mt-2 grid aspect-square w-full place-items-center overflow-hidden rounded-lg border border-slate-200 bg-slate-50 text-2xl font-bold text-slate-400">
                            @if($staff->avatar)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($staff->avatar) }}" alt="{{ $staff->name }}" class="size-full object-cover">
                            @else
                                {{ mb_strtoupper(mb_substr($staff->name, 0, 1)) }}
                            @endif
                        </div>
                    </div>
                </div>
        </section>

        <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center gap-2 border-b border-slate-100 bg-slate-50 px-4 py-3">
                    <x-heroicon-o-lock-closed class="size-5 text-slate-600" />
                    <h3 class="text-sm font-bold text-slate-800">{{ __('UserManagement::app.account_info') }}</h3>
                </div>
                <dl class="grid grid-cols-[7rem_minmax(0,1fr)] gap-x-3 gap-y-4 p-4 text-sm">
                    <dt class="font-medium text-slate-500">{{ __('UserManagement::app.fields.email') }}</dt>
                    <dd class="break-all font-semibold text-slate-900">{{ $staff->email }}</dd>
                    <dt class="font-medium text-slate-500">{{ __('UserManagement::app.fields.password') }}</dt>
                    <dd class="font-semibold tracking-widest text-slate-700" aria-label="{{ __('UserManagement::app.password_hidden') }}">••••••••</dd>
                </dl>
        </section>
        </div>
    </div>
@endsection
