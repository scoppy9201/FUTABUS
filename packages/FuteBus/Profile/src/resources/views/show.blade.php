@extends('core::layouts.home')

@section('title', __('Profile::app.title'))

@section('content')
    @php
        $phone = $user->phone && str_starts_with($user->phone, '+84') ? '0'.substr($user->phone, 3) : $user->phone;
        $profileFieldValue = static function (string $field, ?string $default = null): string {
            $value = old($field, $default);

            return is_string($value) ? $value : ($default ?? '');
        };
    @endphp

    <div class="home-page min-h-screen bg-white">
        @include('core::partials.home.navbar')

        <main class="mx-auto grid w-full max-w-282 gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[255px_minmax(0,1fr)] lg:gap-8 lg:px-0">
            @include('Profile::partials.account-sidebar')

            <section aria-labelledby="profile-title">
                <h1 id="profile-title" class="text-3xl font-semibold text-gray-950">{{ __('Profile::app.title') }}</h1>
                <p class="mt-2 text-base font-medium text-slate-600">{{ __('Profile::app.description') }}</p>

                @if(session('status'))
                    <div hidden data-notice-on-load data-notice-tone="success"
                        data-notice-title="{{ __('Profile::app.title') }}"
                        data-notice-message="{{ session('status') }}"></div>
                @endif
                @if($errors->any())
                    <div role="alert" class="mt-5 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
                        <ul class="list-inside list-disc">
                            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <form
                    action="{{ route('profile.update') }}"
                    method="post"
                    enctype="multipart/form-data"
                    x-data="{ editing: @js($errors->any()), preview: null, fileName: '' }"
                    x-ref="profileForm"
                    class="mt-7 grid gap-8 rounded-2xl border border-gray-200 bg-white p-6 sm:p-8 lg:grid-cols-[220px_minmax(0,1fr)] lg:gap-10"
                >
                    @csrf
                    @method('PUT')

                    <div class="flex flex-col items-center text-center">
                        <div class="grid size-44 place-items-center overflow-hidden rounded-full bg-futa-orange-soft text-futa-orange sm:size-48">
                            <img x-cloak x-show="preview" :src="preview" alt="" class="h-full w-full object-cover">
                            @if($user->avatar)
                                <img x-show="!preview" src="{{ route('profile.avatar') }}" alt="" class="h-full w-full object-cover">
                            @else
                                <img x-show="!preview" src="{{ asset('images/auth/Joyful%20Victory%20Against%20Turquoise%20Wall.png') }}" alt="" class="h-full w-full object-cover">
                            @endif
                        </div>
                        <button
                            x-show="!editing"
                            type="button"
                            disabled
                            class="mt-5 inline-flex min-h-11 items-center justify-center rounded-full border border-gray-200 bg-white px-8 text-base font-medium text-gray-950 disabled:opacity-100"
                        >
                            {{ __('Profile::app.choose_photo') }}
                        </button>
                        <label for="avatar" x-cloak x-show="editing" class="mt-5 inline-flex min-h-11 cursor-pointer items-center justify-center rounded-full border border-gray-200 bg-white px-8 text-base font-medium text-gray-950 transition hover:border-futa-orange hover:text-futa-orange">
                            {{ __('Profile::app.choose_photo') }}
                        </label>
                        <input
                            id="avatar"
                            x-ref="avatar"
                            name="avatar"
                            type="file"
                            accept="image/jpeg,image/png"
                            :disabled="!editing"
                            class="sr-only"
                            @change="const file = $event.target.files[0]; if (file) { preview = URL.createObjectURL(file); fileName = file.name }"
                        >
                        <span x-cloak x-show="fileName" x-text="fileName" class="mt-2 max-w-full truncate text-xs text-slate-600"></span>
                        <p class="mt-4 text-sm leading-5 text-slate-600">{{ __('Profile::app.photo_hint') }}</p>
                    </div>

                    <div class="space-y-2.5">
                        <div class="grid min-h-11 gap-1.5 sm:grid-cols-[110px_8px_minmax(0,1fr)] sm:items-center sm:gap-3">
                            <label for="profile-name" class="text-base font-medium text-slate-600">{{ __('Profile::app.name') }}</label>
                            <span class="hidden text-base text-gray-900 sm:block">:</span>
                            <span x-show="!editing" class="min-w-0 px-3 text-base font-semibold wrap-break-word text-gray-950">{{ $user->name }}</span>
                            <div x-cloak x-show="editing" class="relative min-w-0">
                                <input
                                    id="profile-name"
                                    x-ref="name" :disabled="!editing"
                                    name="name" type="text" value="{{ $profileFieldValue('name', $user->name) }}"
                                    required maxlength="100"
                                    class="h-10 w-full min-w-0 rounded-lg border border-transparent px-3 pr-11 text-base font-semibold text-gray-950 hover:border-gray-200 focus:border-futa-orange focus:outline-none"
                                >
                                <x-heroicon-o-pencil-square class="pointer-events-none absolute right-3 top-1/2 size-5 -translate-y-1/2 text-slate-400" />
                            </div>
                        </div>
                        <div class="grid min-h-11 gap-1.5 sm:grid-cols-[110px_8px_minmax(0,1fr)] sm:items-center sm:gap-3">
                            <label for="profile-phone" class="text-base font-medium text-slate-600">{{ __('Profile::app.phone') }}</label>
                            <span class="hidden text-base text-gray-900 sm:block">:</span>
                            <span x-show="!editing" class="min-w-0 px-3 text-base font-semibold text-gray-950">{{ $phone }}</span>
                            <div x-cloak x-show="editing" class="relative min-w-0">
                                <input
                                    id="profile-phone"
                                    :disabled="!editing"
                                    name="phone" type="tel" value="{{ $profileFieldValue('phone', $phone) }}"
                                    required autocomplete="tel"
                                    class="h-10 w-full min-w-0 rounded-lg border border-transparent px-3 pr-11 text-base font-semibold text-gray-950 hover:border-gray-200 focus:border-futa-orange focus:outline-none"
                                >
                                <x-heroicon-o-pencil-square class="pointer-events-none absolute right-3 top-1/2 size-5 -translate-y-1/2 text-slate-400" />
                            </div>
                        </div>
                        <div class="grid min-h-11 gap-1.5 sm:grid-cols-[110px_8px_minmax(0,1fr)] sm:items-center sm:gap-3">
                            <label for="profile-gender" class="text-base font-medium text-slate-600">{{ __('Profile::app.gender') }}</label>
                            <span class="hidden text-base text-gray-900 sm:block">:</span>
                            <select
                                id="profile-gender"
                                name="gender"
                                :disabled="!editing"
                                class="h-10 min-w-0 rounded-lg border border-gray-200 bg-white px-3 text-base font-medium text-gray-950 disabled:opacity-100 focus:border-futa-orange focus:outline-none sm:max-w-40"
                            >
                                <option value=""></option>
                                @foreach(['male', 'female', 'other'] as $gender)
                                    <option value="{{ $gender }}" @selected($profileFieldValue('gender', $user->gender) === $gender)>{{ __('Profile::app.'.$gender) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid min-h-11 gap-1.5 sm:grid-cols-[110px_8px_minmax(0,1fr)] sm:items-center sm:gap-3">
                            <span class="text-base font-medium text-slate-600">{{ __('Profile::app.email') }}</span>
                            <span class="hidden text-base text-gray-900 sm:block">:</span>
                            <span class="min-w-0 px-3 text-base font-semibold wrap-break-word text-gray-950">{{ $user->email }}</span>
                        </div>
                        <div class="grid min-h-11 gap-1.5 sm:grid-cols-[110px_8px_minmax(0,1fr)] sm:items-center sm:gap-3">
                            <label for="profile-birth-date" class="text-base font-medium text-slate-600">{{ __('Profile::app.birth_date') }}</label>
                            <span class="hidden text-base text-gray-900 sm:block">:</span>
                            <span x-show="!editing" class="min-w-0 px-3 text-base font-semibold text-gray-950">{{ $user->date_of_birth?->format('d/m/Y') ?? '—' }}</span>
                            <div x-cloak x-show="editing" data-profile-date-picker data-max="{{ now()->toDateString() }}" class="relative min-w-0 sm:w-52">
                                <input
                                    id="profile-birth-date"
                                    name="date_of_birth"
                                    type="hidden"
                                    :disabled="!editing"
                                    value="{{ $profileFieldValue('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}"
                                    data-profile-date-value
                                >
                                <button
                                    type="button"
                                    data-profile-date-toggle
                                    aria-haspopup="dialog"
                                    aria-controls="profile-birth-date-calendar"
                                    aria-expanded="false"
                                    class="flex h-10 w-full items-center justify-between gap-2 rounded-lg border border-gray-200 bg-white px-3 text-left text-base font-medium text-gray-950 hover:border-futa-orange focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-futa-orange"
                                >
                                    <span data-profile-date-label>{{ __('Profile::app.choose_date') }}</span>
                                    <x-heroicon-o-calendar-days class="size-5 shrink-0 text-slate-400" />
                                </button>
                                <div
                                    data-profile-calendar
                                    id="profile-birth-date-calendar"
                                    hidden
                                    role="dialog"
                                    aria-label="{{ __('Profile::app.birth_date') }}"
                                    class="absolute right-0 top-full z-30 mt-2 w-72 rounded-xl border border-gray-200 bg-white p-3 text-gray-900 shadow-xl sm:right-auto sm:left-0"
                                >
                                    <div class="mb-3 flex items-center justify-between gap-2">
                                        <button type="button" data-profile-date-previous aria-label="{{ __('Profile::app.previous_month') }}" class="grid size-8 place-items-center rounded-full text-futa-orange hover:bg-futa-orange-soft focus-visible:outline-2 focus-visible:outline-futa-orange">
                                            <x-heroicon-o-chevron-left class="size-4" />
                                        </button>
                                        <span data-profile-date-month class="text-sm font-semibold text-gray-950"></span>
                                        <input
                                            type="number"
                                            data-profile-date-year
                                            aria-label="{{ __('Profile::app.year') }}"
                                            min="1000"
                                            max="{{ now()->year }}"
                                            class="w-17 rounded-md border border-gray-200 px-1 py-1 text-center text-sm font-semibold text-gray-950 focus:border-futa-orange focus:outline-none"
                                        >
                                        <button type="button" data-profile-date-next aria-label="{{ __('Profile::app.next_month') }}" class="grid size-8 place-items-center rounded-full text-futa-orange hover:bg-futa-orange-soft focus-visible:outline-2 focus-visible:outline-futa-orange">
                                            <x-heroicon-o-chevron-right class="size-4" />
                                        </button>
                                    </div>
                                    <div data-profile-date-weekdays class="grid grid-cols-7 text-center text-xs font-semibold text-slate-500"></div>
                                    <div data-profile-date-days class="mt-1 grid grid-cols-7 gap-0.5"></div>
                                    <button type="button" data-profile-date-clear class="mt-3 w-full rounded-lg py-1.5 text-sm font-semibold text-futa-orange hover:bg-futa-orange-soft focus-visible:outline-2 focus-visible:outline-futa-orange">
                                        {{ __('Profile::app.clear_date') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="grid min-h-11 gap-1.5 sm:grid-cols-[110px_8px_minmax(0,1fr)] sm:items-center sm:gap-3">
                            <label for="profile-address" class="text-base font-medium text-slate-600">{{ __('Profile::app.address') }}</label>
                            <span class="hidden text-base text-gray-900 sm:block">:</span>
                            <span x-show="!editing" class="min-w-0 px-3 text-base font-semibold wrap-break-word text-gray-950">{{ $user->address ?: '—' }}</span>
                            <div x-cloak x-show="editing" class="relative min-w-0">
                                <input
                                    id="profile-address"
                                    :disabled="!editing"
                                    name="address" type="text" value="{{ $profileFieldValue('address', $user->address) }}"
                                    maxlength="255"
                                    class="h-10 w-full min-w-0 rounded-lg border border-transparent px-3 pr-11 text-base font-semibold text-gray-950 hover:border-gray-200 focus:border-futa-orange focus:outline-none"
                                >
                                <x-heroicon-o-pencil-square class="pointer-events-none absolute right-3 top-1/2 size-5 -translate-y-1/2 text-slate-400" />
                            </div>
                        </div>
                        <div class="grid min-h-11 gap-1.5 sm:grid-cols-[110px_8px_minmax(0,1fr)] sm:items-center sm:gap-3">
                            <label for="profile-occupation" class="text-base font-medium text-slate-600">{{ __('Profile::app.occupation') }}</label>
                            <span class="hidden text-base text-gray-900 sm:block">:</span>
                            <span x-show="!editing" class="min-w-0 px-3 text-base font-semibold wrap-break-word text-gray-950">{{ $user->occupation ?: '—' }}</span>
                            <div x-cloak x-show="editing" class="relative min-w-0">
                                <input
                                    id="profile-occupation"
                                    :disabled="!editing"
                                    name="occupation" type="text" value="{{ $profileFieldValue('occupation', $user->occupation) }}"
                                    maxlength="255"
                                    class="h-10 w-full min-w-0 rounded-lg border border-transparent px-3 pr-11 text-base font-semibold text-gray-950 hover:border-gray-200 focus:border-futa-orange focus:outline-none"
                                >
                                <x-heroicon-o-pencil-square class="pointer-events-none absolute right-3 top-1/2 size-5 -translate-y-1/2 text-slate-400" />
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center justify-center gap-3 pt-4">
                            <button
                                x-show="!editing"
                                type="button"
                                @click="editing = true; $nextTick(() => $refs.name.focus())"
                                class="inline-flex min-h-11 min-w-44 items-center justify-center rounded-full bg-futa-orange px-8 text-base font-semibold text-white transition hover:bg-futa-orange-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-futa-orange"
                            >
                                {{ __('Profile::app.update') }}
                            </button>
                            <button
                                x-cloak x-show="editing"
                                type="button"
                                @click="$refs.profileForm.reset(); preview = null; fileName = ''; editing = false"
                                class="inline-flex min-h-11 items-center justify-center rounded-full border border-gray-200 px-6 text-base font-semibold text-gray-800 transition hover:bg-gray-50"
                            >
                                {{ __('Profile::app.cancel') }}
                            </button>
                            <button
                                x-cloak x-show="editing"
                                type="submit"
                                class="inline-flex min-h-11 min-w-44 items-center justify-center gap-2 rounded-full bg-futa-orange px-8 text-base font-semibold text-white transition hover:bg-futa-orange-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-futa-orange"
                            >
                                <x-heroicon-o-pencil-square class="size-5" />
                                {{ __('Profile::app.update') }}
                            </button>
                        </div>
                    </div>
                </form>
            </section>
        </main>

        @include('core::partials.home.footer')
    </div>
    @vite('packages/FuteBus/Profile/src/resources/js/app.js')
@endsection
