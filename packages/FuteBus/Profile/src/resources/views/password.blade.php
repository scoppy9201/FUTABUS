@extends('core::layouts.home')

@section('title', __('Profile::app.password_change.title'))

@section('content')
    <div class="home-page min-h-screen bg-white">
        @include('core::partials.home.navbar')

        <main class="mx-auto grid w-full max-w-282 gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[255px_minmax(0,1fr)] lg:gap-8 lg:px-0">
            @include('Profile::partials.account-sidebar')

            <section class="w-full max-w-xl justify-self-center" aria-labelledby="password-title">
                <h1 id="password-title" class="text-3xl font-semibold text-gray-950">{{ __('Profile::app.password_change.title') }}</h1>
                <p class="mt-2 text-base font-medium text-slate-600">{{ __('Profile::app.password_change.description') }}</p>

                @if(session('status'))
                    <p role="status" class="mt-5 rounded-lg bg-green-50 px-4 py-3 text-sm font-medium text-green-800">{{ session('status') }}</p>
                @endif
                @if(session('warning'))
                    <p role="alert" class="mt-5 rounded-lg bg-amber-50 px-4 py-3 text-sm font-medium text-amber-900">{{ session('warning') }}</p>
                @endif

                <form action="{{ route('profile.password.update') }}" method="post" class="mt-7 min-h-134 rounded-2xl border border-gray-200 bg-white p-6 sm:p-8">
                    @csrf
                    @method('PUT')

                    <p class="mb-10 text-center text-2xl font-semibold wrap-anywhere text-gray-950">{{ $user->email }}</p>

                    <div class="space-y-7">
                        @foreach(['current_password', 'password', 'password_confirmation'] as $field)
                            <div x-data="{ visible: false }">
                                <label for="{{ $field }}" class="mb-2 block text-base font-medium text-gray-950">
                                    <span aria-hidden="true" class="text-red-500">*</span>
                                    {{ __('Profile::app.password_change.fields.'.$field) }}
                                </label>
                                <div class="relative">
                                    <input
                                        id="{{ $field }}"
                                        name="{{ $field }}"
                                        type="password"
                                        :type="visible ? 'text' : 'password'"
                                        required
                                        @if($field === 'password') minlength="8" @endif
                                        autocomplete="{{ $field === 'current_password' ? 'current-password' : 'new-password' }}"
                                        placeholder="{{ __('Profile::app.password_change.placeholders.'.$field) }}"
                                        @class([
                                            'h-11 w-full rounded-lg border bg-white px-3 pr-11 text-base text-gray-950 placeholder:text-gray-400 focus:outline-2 focus:outline-offset-1 focus:outline-[#ef5222]',
                                            'border-red-500' => $errors->has($field),
                                            'border-gray-200' => ! $errors->has($field),
                                        ])
                                    >
                                    <button
                                        type="button"
                                        @click="visible = !visible"
                                        :aria-label="visible ? @js(__('Profile::app.password_change.hide_password')) : @js(__('Profile::app.password_change.show_password'))"
                                        class="absolute inset-y-0 right-0 grid w-11 place-items-center text-gray-400 hover:text-[#ef5222] focus-visible:outline-2 focus-visible:outline-[#ef5222]"
                                    >
                                        <x-heroicon-o-eye-slash x-show="!visible" class="size-5" />
                                        <x-heroicon-o-eye x-cloak x-show="visible" class="size-5" />
                                    </button>
                                </div>
                                @error($field)
                                    <p role="alert" class="mt-1 text-sm font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-12 flex justify-center gap-4">
                        <a href="{{ route('profile.show') }}" class="inline-flex min-h-11 min-w-32 items-center justify-center rounded-full border border-gray-200 px-6 text-base font-medium text-gray-900 hover:bg-gray-50 focus-visible:outline-2 focus-visible:outline-[#ef5222]">
                            {{ __('Profile::app.cancel') }}
                        </a>
                        <button type="submit" class="inline-flex min-h-11 min-w-32 items-center justify-center rounded-full bg-[#ef5222] px-6 text-base font-semibold text-white hover:bg-[#d94317] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#ef5222]">
                            {{ __('Profile::app.password_change.confirm') }}
                        </button>
                    </div>
                </form>
            </section>
        </main>

        @include('core::partials.home.footer')
    </div>
@endsection
