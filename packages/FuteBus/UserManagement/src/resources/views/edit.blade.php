@extends('Dashboard::layouts.admin')

@section('title', __('UserManagement::app.edit'))
@section('page_title', __('UserManagement::app.edit'))

@section('content')
    <div class="flex items-center gap-3 rounded-xl border border-orange-100 bg-gradient-to-r from-orange-50 to-rose-50 px-5 py-4">
        <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-[#F26522] text-white"><x-heroicon-o-user-plus class="size-5" /></span>
        <div>
            <h1 class="text-xl font-extrabold tracking-tight text-slate-900 sm:text-2xl">{{ __('UserManagement::app.edit') }}</h1>
            <p class="mt-0.5 text-sm text-slate-600">{{ __('UserManagement::app.edit_description') }}</p>
        </div>
    </div>

    <form method="post" action="{{ route('staff-accounts.update', $staff->id) }}" enctype="multipart/form-data" novalidate x-data="{ avatarPreview: null, avatarFileName: '' }" class="mt-4 w-full rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        @csrf
        @method('put')
        @include('UserManagement::partials.form', ['staff' => $staff, 'isCreate' => false])
    </form>
@endsection
