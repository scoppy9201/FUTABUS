@extends('StaffDashboard::layouts.staff')

@section('title', __('StaffDashboard::staff.change_password'))

@push('styles')
    @vite(['packages/FuteBus/StaffDashboard/src/resources/css/app.css', 'packages/FuteBus/StaffDashboard/src/resources/js/app.js'])
@endpush

@section('content')
    <div
        x-data="staffPassword(@js(['show' => route('api.v1.staff.me.show'),
            'avatar' => route('api.v1.staff.me.avatar'),
            'dashboard' => route('api.v1.staff.dashboard.show'),
            'update' => route('api.v1.staff.me.password.update')]),
            @js(['error' => __('StaffDashboard::staff.error'),
            'validation' => __('Profile::app.password_change.validation')]))"
        class="flex h-dvh min-h-0 flex-col overflow-hidden bg-gray-50 print:h-auto print:overflow-visible">
        @include('StaffDashboard::partials.header')
        @include('StaffDashboard::partials.notifications')
        <div class="staff-shell grid min-h-0 min-w-0 flex-1 overflow-hidden grid-cols-1 bg-[#F8F9FA] text-sm lg:grid-cols-[16rem_minmax(0,1fr)] lg:items-stretch [&>main]:min-h-0 [&>main]:overflow-y-auto [&>main]:p-4 md:[&>main]:p-6 lg:[&>main]:p-8 print:block print:overflow-visible print:[&>main]:overflow-visible print:[&>main]:p-0">
            @include('StaffDashboard::partials.sidebar')
            <main class="flex min-w-0 items-start p-2 lg:min-h-0">
                <section class="flex w-full min-w-0 flex-col rounded-xl border border-gray-200 bg-white p-5 shadow-sm xl:p-8">
                    <div class="flex items-center gap-3">
                        <span class="grid size-12 shrink-0 place-items-center rounded-full bg-orange-50 text-futa-orange"><x-heroicon-o-lock-closed class="size-6" /></span>
                        <div><h1 class="text-2xl font-bold text-gray-900">{{ __('StaffDashboard::staff.change_password') }}</h1><p class="mt-1 text-base text-gray-500" x-text="profile.email"></p></div>
                    </div>
                    <p class="mt-2 text-sm text-gray-500">{{ __('Profile::app.password_change.description') }}</p>
                    <p x-show="loading" role="status" class="mt-2 text-sm text-gray-500">{{ __('StaffDashboard::staff.loading') }}</p>
                    <form x-ref="form" @submit.prevent="save()" novalidate class="mt-5 flex min-h-0 flex-col">
                        <fieldset :disabled="loading || saving || !employee" class="grid gap-4">
                            @foreach(['current_password' => 'current-password', 'password' => 'new-password', 'password_confirmation' => 'new-password'] as $field => $autocomplete)
                                <div x-data="{ visible: false }">
                                    <label for="staff-{{ $field }}" class="mb-1 block text-base leading-5 font-medium text-gray-700">{{ __('Profile::app.password_change.fields.'.$field) }}</label>
                                    <div class="relative">
                                        <input
                                            id="staff-{{ $field }}"
                                            name="{{ $field }}"
                                            :type="visible ? 'text' : 'password'"
                                            autocomplete="{{ $autocomplete }}"
                                            x-model="passwords.{{ $field }}"
                                            @input="validate(@js($field))"
                                            @blur="validate(@js($field))" required @if($field !== 'current_password')
                                            minlength="8" @endif
                                            :aria-invalid="!!errors[@js($field)]"
                                            class="w-full rounded-lg border border-gray-200 h-12 px-4 pr-12 text-base focus:border-futa-orange focus:outline-none">
                                        <button
                                            type="button"
                                            @click="visible = !visible"
                                            :aria-pressed="visible"
                                            :aria-label="visible ? @js(__('Profile::app.password_change.hide_password')) : @js(__('Profile::app.password_change.show_password'))"
                                            class="absolute inset-y-0 right-0 grid w-12 place-items-center text-gray-400 focus-visible:outline-2 focus-visible:outline-futa-orange">
                                        <x-heroicon-o-eye
                                            x-show="!visible"
                                            class="size-5" />
                                        <x-heroicon-o-eye-slash
                                            x-show="visible" x-cloak
                                            class="size-5" />
                                        </button>
                                    </div>
                                    <p x-show="errors[@js($field)]" x-text="errors[@js($field)]?.[0]" class="mt-1 text-xs text-red-600"></p>
                                </div>
                            @endforeach
                        </fieldset>
                        <div class="mt-4 flex justify-end gap-3 border-t border-gray-100 pt-3">
                            <a href="{{ route('staff.dashboard') }}" class="rounded-lg border border-gray-200 px-8 py-2 text-base font-semibold">{{ __('Profile::app.cancel') }}</a>
                            <button type="submit" :disabled="loading || saving || !employee" class="rounded-lg bg-futa-orange px-6 py-2 text-base font-semibold text-white disabled:opacity-50">{{ __('StaffDashboard::staff.change_password') }}</button>
                        </div>
                    </form>
                </section>
            </main>
        </div>
    </div>
@endsection
