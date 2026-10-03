@extends('core::layouts.home')

@section('title', __('Profile::app.title'))

@section('content')
    @php
        $phone = $user->phone && str_starts_with($user->phone, '+84') ? '0'.substr($user->phone, 3) : $user->phone;
    @endphp

    <div class="home-page min-h-screen bg-white">
        @include('core::partials.home.navbar')

        <main class="mx-auto grid w-full max-w-282 gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[255px_minmax(0,1fr)] lg:gap-8 lg:px-0">
            <aside class="rounded-2xl border border-gray-200 bg-white p-2.5 lg:min-h-134" aria-label="{{ __('Profile::app.title') }}">
                <div class="space-y-1.5">
                    <div class="flex items-center gap-3 rounded-xl px-3 py-3 text-lg font-medium text-gray-900" aria-disabled="true" title="{{ __('Profile::app.coming_soon') }}">
                        <span class="grid size-9 shrink-0 place-items-center overflow-hidden rounded-full bg-[#00613d]"><img src="{{ asset('images/auth/White%20Brushstroke%20F%20on%20Forest%20Green.png') }}" alt="" class="size-full scale-125 object-cover"></span>
                        <span>{{ __('Profile::app.futapay') }}</span>
                    </div>
                    <a href="{{ route('profile.show') }}" aria-current="page" class="flex items-center gap-3 rounded-xl bg-orange-50 px-3 py-3 text-lg font-semibold text-gray-950">
                        <span class="grid size-9 shrink-0 place-items-center rounded-full bg-amber-400 text-white"><x-heroicon-s-user-circle class="size-7" /></span>
                        <span>{{ __('Profile::app.title') }}</span>
                    </a>
                    <div class="flex items-center gap-3 rounded-xl px-3 py-3 text-lg font-medium text-gray-900" aria-disabled="true" title="{{ __('Profile::app.coming_soon') }}">
                        <span class="grid size-9 shrink-0 place-items-center rounded-full bg-sky-500 text-white"><x-heroicon-o-clock class="size-6" /></span>
                        <span>{{ __('Profile::app.ticket_history') }}</span>
                    </div>
                    <div class="flex items-center gap-3 rounded-xl px-3 py-3 text-lg font-medium text-gray-900" aria-disabled="true" title="{{ __('Profile::app.coming_soon') }}">
                        <span class="grid size-9 shrink-0 place-items-center rounded-full bg-[#ef6b31] text-white"><x-heroicon-o-lock-closed class="size-6" /></span>
                        <span>{{ __('Profile::app.reset_password') }}</span>
                    </div>
                    <form action="{{ route('logout') }}" method="post">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left text-lg font-medium text-gray-900 transition hover:bg-orange-50">
                            <span class="grid size-9 shrink-0 place-items-center rounded-full bg-red-600 text-white"><x-heroicon-o-arrow-right-on-rectangle class="size-6" /></span>
                            <span>{{ __('Profile::app.logout') }}</span>
                        </button>
                    </form>
                </div>
            </aside>

            <section aria-labelledby="profile-title">
                <h1 id="profile-title" class="text-3xl font-semibold text-gray-950">{{ __('Profile::app.title') }}</h1>
                <p class="mt-2 text-base font-medium text-slate-600">{{ __('Profile::app.description') }}</p>

                @if(session('status'))
                    <p role="status" class="mt-5 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</p>
                @endif
                @if($errors->any())
                    <div role="alert" class="mt-5 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
                        <ul class="list-inside list-disc">
                            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('profile.update') }}" method="post" enctype="multipart/form-data" x-data="{ preview: null, fileName: '' }" class="mt-7 grid gap-8 rounded-2xl border border-gray-200 bg-white p-6 sm:p-8 lg:grid-cols-[220px_minmax(0,1fr)] lg:gap-10">
                    @csrf
                    @method('PUT')

                    <div class="flex flex-col items-center text-center">
                        <div class="grid size-44 place-items-center overflow-hidden rounded-full bg-orange-50 text-[#ef5222] sm:size-48">
                            <img x-cloak x-show="preview" :src="preview" alt="" class="h-full w-full object-cover">
                            @if($user->avatar)
                                <img x-show="!preview" src="{{ route('profile.avatar') }}" alt="" class="h-full w-full object-cover">
                            @else
                                <img x-show="!preview" src="{{ asset('images/auth/Joyful%20Victory%20Against%20Turquoise%20Wall.png') }}" alt="" class="h-full w-full object-cover">
                            @endif
                        </div>
                        <label for="avatar" class="mt-5 inline-flex min-h-11 cursor-pointer items-center justify-center rounded-full border border-gray-200 bg-white px-8 text-base font-medium text-gray-950 transition hover:border-[#ef5222] hover:text-[#ef5222]">
                            {{ __('Profile::app.choose_photo') }}
                        </label>
                        <input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png" class="sr-only" @change="const file = $event.target.files[0]; if (file) { preview = URL.createObjectURL(file); fileName = file.name }">
                        <span x-cloak x-show="fileName" x-text="fileName" class="mt-2 max-w-full truncate text-xs text-slate-600"></span>
                        <p class="mt-4 text-sm leading-5 text-slate-600">{{ __('Profile::app.photo_hint') }}</p>
                    </div>

                    <div class="space-y-1">
                        <div class="grid gap-1.5 sm:grid-cols-[110px_8px_minmax(0,1fr)] sm:items-center sm:gap-3">
                            <label for="profile-name" class="text-base font-medium text-slate-600">{{ __('Profile::app.name') }}</label>
                            <span class="hidden text-base text-gray-900 sm:block">:</span>
                            <input id="profile-name" name="name" type="text" value="{{ old('name', $user->name) }}" required maxlength="100" class="h-9 min-w-0 rounded-lg border border-transparent px-3 text-base font-semibold text-gray-950 hover:border-gray-200 focus:border-[#ef5222] focus:outline-none">
                        </div>
                        <div class="grid gap-1.5 sm:grid-cols-[110px_8px_minmax(0,1fr)] sm:items-center sm:gap-3">
                            <label for="profile-phone" class="text-base font-medium text-slate-600">{{ __('Profile::app.phone') }}</label>
                            <span class="hidden text-base text-gray-900 sm:block">:</span>
                            <input id="profile-phone" name="phone" type="tel" value="{{ old('phone', $phone) }}" required autocomplete="tel" class="h-9 min-w-0 rounded-lg border border-transparent px-3 text-base font-semibold text-gray-950 hover:border-gray-200 focus:border-[#ef5222] focus:outline-none">
                        </div>
                        <div class="grid gap-1.5 sm:grid-cols-[110px_8px_minmax(0,1fr)] sm:items-center sm:gap-3">
                            <label for="profile-gender" class="text-base font-medium text-slate-600">{{ __('Profile::app.gender') }}</label>
                            <span class="hidden text-base text-gray-900 sm:block">:</span>
                            <select id="profile-gender" name="gender" class="h-9 min-w-0 rounded-lg border border-gray-200 bg-white px-3 text-base font-medium text-gray-950 focus:border-[#ef5222] focus:outline-none sm:max-w-40">
                                <option value="">{{ __('Profile::app.gender_blank') }}</option>
                                @foreach(['male', 'female', 'other'] as $gender)
                                    <option value="{{ $gender }}" @selected(old('gender', $user->gender) === $gender)>{{ __('Profile::app.'.$gender) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid gap-1.5 sm:grid-cols-[110px_8px_minmax(0,1fr)] sm:items-center sm:gap-3">
                            <label for="profile-email" class="text-base font-medium text-slate-600">{{ __('Profile::app.email') }}</label>
                            <span class="hidden text-base text-gray-900 sm:block">:</span>
                            <input id="profile-email" type="email" value="{{ $user->email }}" readonly class="h-9 min-w-0 rounded-lg border border-transparent bg-transparent px-3 text-base font-semibold text-gray-950">
                        </div>
                        <div class="grid gap-1.5 sm:grid-cols-[110px_8px_minmax(0,1fr)] sm:items-center sm:gap-3">
                            <label for="profile-birth-date" class="text-base font-medium text-slate-600">{{ __('Profile::app.birth_date') }}</label>
                            <span class="hidden text-base text-gray-900 sm:block">:</span>
                            <input id="profile-birth-date" name="date_of_birth" type="date" value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}" max="{{ now()->toDateString() }}" class="h-9 min-w-0 rounded-lg border border-transparent px-3 text-base font-semibold text-gray-950 hover:border-gray-200 focus:border-[#ef5222] focus:outline-none sm:max-w-52">
                        </div>
                        <div class="grid gap-1.5 sm:grid-cols-[110px_8px_minmax(0,1fr)] sm:items-center sm:gap-3">
                            <label for="profile-address" class="text-base font-medium text-slate-600">{{ __('Profile::app.address') }}</label>
                            <span class="hidden text-base text-gray-900 sm:block">:</span>
                            <input id="profile-address" name="address" type="text" value="{{ old('address', $user->address) }}" maxlength="255" class="h-9 min-w-0 rounded-lg border border-transparent px-3 text-base font-semibold text-gray-950 hover:border-gray-200 focus:border-[#ef5222] focus:outline-none">
                        </div>
                        <div class="grid gap-1.5 sm:grid-cols-[110px_8px_minmax(0,1fr)] sm:items-center sm:gap-3">
                            <label for="profile-occupation" class="text-base font-medium text-slate-600">{{ __('Profile::app.occupation') }}</label>
                            <span class="hidden text-base text-gray-900 sm:block">:</span>
                            <input id="profile-occupation" name="occupation" type="text" value="{{ old('occupation', $user->occupation) }}" maxlength="255" class="h-9 min-w-0 rounded-lg border border-transparent px-3 text-base font-semibold text-gray-950 hover:border-gray-200 focus:border-[#ef5222] focus:outline-none">
                        </div>
                        <div class="pt-4 text-center">
                            <button type="submit" class="inline-flex min-h-11 min-w-44 items-center justify-center rounded-full bg-[#ef5222] px-8 text-base font-semibold text-white transition hover:bg-[#d94317] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#ef5222]">
                                {{ __('Profile::app.update') }}
                            </button>
                        </div>
                    </div>
                </form>
            </section>
        </main>

        @include('core::partials.home.footer')
    </div>
@endsection
