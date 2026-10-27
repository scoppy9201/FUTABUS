@extends('StaffDashboard::layouts.staff')

@section('title', __('Profile::app.title'))

@push('styles')
    @vite(['packages/FuteBus/StaffDashboard/src/resources/css/app.css', 'packages/FuteBus/StaffDashboard/src/resources/js/app.js'])
@endpush

@section('content')
    <div
        x-data="staffProfile(@js(['show' => route('api.v1.staff.me.show'),
            'update' => route('api.v1.staff.me.update'),
            'avatar' => route('api.v1.staff.me.avatar'),
            'dashboard' => route('api.v1.staff.dashboard.show')]),
            @js(['error' => __('StaffDashboard::staff.error'),
            'updated' => __('Profile::app.updated'),
            'validation' => __('Profile::app.validation')]))"
        class="flex h-dvh min-h-0 flex-col overflow-hidden bg-gray-50 print:h-auto print:overflow-visible">
        @include('StaffDashboard::partials.header')
        @include('StaffDashboard::partials.notifications')
        <div
            class="staff-shell grid min-h-0 min-w-0 flex-1 overflow-hidden grid-cols-1 bg-[#F8F9FA] text-sm lg:grid-cols-[16rem_minmax(0,1fr)] lg:items-stretch [&>main]:min-h-0
            [&>main]:overflow-y-auto [&>main]:p-4 md:[&>main]:p-6 lg:[&>main]:p-8 print:block print:overflow-visible print:[&>main]:overflow-visible print:[&>main]:p-0">
            @include('StaffDashboard::partials.sidebar')
            <main class="min-w-0 p-2 sm:p-6">
            <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                <h1 class="text-2xl font-bold text-gray-900">{{ __('Profile::app.title') }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ __('StaffDashboard::staff.role') }}</p>
                <p x-show="loading" role="status" class="mt-4 text-sm text-gray-500">{{ __('StaffDashboard::staff.loading') }}</p>
                <form x-ref="form" @submit.prevent="save()" novalidate enctype="multipart/form-data" class="mt-6">
                    @csrf
                    <fieldset :disabled="loading || saving || !employee" class="grid min-w-0 gap-6 md:grid-cols-[200px_minmax(0,1fr)]">
                        <div class="text-center">
                            <span class="mx-auto grid size-36 place-items-center overflow-hidden rounded-full bg-gray-50 text-futa-orange">
                                <img x-show="avatarPreview || employee?.avatar_url" x-cloak :src="avatarPreview || employee?.avatar_url || null" alt="" class="size-full object-cover">
                                <x-heroicon-o-user-circle x-show="!avatarPreview && !employee?.avatar_url" class="size-24" />
                            </span>
                            <label for="staff-avatar" class="mt-3 inline-block cursor-pointer rounded-lg border border-gray-200 px-3 py-2 text-base">{{ __('Profile::app.choose_photo') }}</label>
                            <input x-ref="avatar" id="staff-avatar" @change="previewAvatar()" type="file" name="avatar" accept="image/jpeg,image/png" class="sr-only">
                            <p class="mt-3 text-xs text-gray-500">{{ __('Profile::app.photo_hint') }}</p>
                            <p x-show="errors.avatar" x-text="errors.avatar?.[0]" class="mt-2 text-xs text-red-600"></p>
                        </div>
                        <div class="grid min-w-0 gap-5 sm:grid-cols-2">
                            @foreach(['name' => ['name', 'text'], 'phone' => ['phone', 'tel'], 'date_of_birth' => ['birth_date', 'date'], 'address' => ['address', 'text']] as $field => [$label, $type])
                                <div>
                                    <label for="staff-{{ $field }}" class="mb-1 block text-base leading-5 font-medium text-gray-700">{{ __('Profile::app.'.$label) }}</label>
                                    <input
                                        id="staff-{{ $field }}"
                                        name="{{ $field }}"
                                        type="{{ $type }}"
                                        x-model="profile.{{ $field }}"
                                        @input="validate(@js($field))"
                                        @blur="validate(@js($field))" @if(in_array($field, ['name', 'phone'])) required @endif
                                        :aria-invalid="!!errors[@js($field)]"
                                        class="w-full rounded-lg border border-gray-200 h-12 px-4 text-base focus:border-futa-orange focus:outline-none">
                                    <p x-show="errors[@js($field)]" x-text="errors[@js($field)]?.[0]" class="mt-1 text-xs text-red-600"></p>
                                </div>
                            @endforeach
                            <div>
                                <label for="staff-gender" class="mb-1 block text-base leading-5 font-medium text-gray-700">{{ __('Profile::app.gender') }}</label>
                                <select id="staff-gender" name="gender" x-model="profile.gender" class="w-full rounded-lg border border-gray-200 h-12 px-4 text-base focus:border-futa-orange focus:outline-none">
                                    <option value="">{{ __('Profile::app.gender_blank') }}</option>
                                    @foreach(['male', 'female', 'other'] as $gender)<option value="{{ $gender }}">{{ __('Profile::app.'.$gender) }}</option>@endforeach
                                </select>
                                <p x-show="errors.gender" x-text="errors.gender?.[0]" class="mt-1 text-xs text-red-600"></p>
                            </div>
                            <div>
                                <label for="staff-email" class="mb-1 block text-base leading-5 font-medium text-gray-700">{{ __('Profile::app.email') }}</label>
                                <input id="staff-email" type="email" x-model="profile.email" readonly class="w-full rounded-lg border border-gray-200 bg-gray-50 h-12 px-4 text-base text-gray-500">
                            </div>
                        </div>
                    </fieldset>
                    <div class="mt-8 flex justify-end gap-3 border-t border-gray-100 pt-5">
                        <a href="{{ route('staff.dashboard') }}" class="rounded-lg border border-gray-200 px-10 py-3 text-base font-semibold">{{ __('Profile::app.cancel') }}</a>
                        <button type="submit" :disabled="loading || saving || !employee" class="rounded-lg bg-futa-orange px-10 py-3 text-base font-semibold text-white disabled:opacity-50">{{ __('Profile::app.update') }}</button>
                    </div>
                </form>
            </section>
        </main>
        </div>
    </div>
@endsection
