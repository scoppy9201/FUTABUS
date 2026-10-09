<div class="grid gap-4 xl:grid-cols-2 xl:items-stretch">
<section class="rounded-xl border border-slate-200 bg-white">
    <div class="flex items-center gap-2 border-b border-slate-100 bg-slate-50 px-4 py-3">
        <x-heroicon-o-user class="size-5 text-slate-600" />
        <h2 class="text-sm font-bold text-slate-800">{{ __('UserManagement::app.basic_info') }}</h2>
    </div>
    <div class="space-y-4 p-4 sm:p-5 xl:space-y-3 xl:p-4">
        <div class="flex flex-wrap items-center gap-4">
            <div class="relative size-20 shrink-0 xl:size-16">
                <div class="grid size-20 place-items-center overflow-hidden rounded-full border border-slate-200 bg-slate-50 text-slate-400 xl:size-16">
                    <img x-cloak x-show="avatarPreview" :src="avatarPreview" alt="" class="size-full object-cover">
                    @if($staff?->avatar)
                        <img x-show="!avatarPreview" src="{{ asset('storage/'.$staff->avatar) }}" alt="" class="size-full object-cover">
                    @else
                        <span x-show="!avatarPreview"><x-heroicon-o-user class="size-9" /></span>
                    @endif
                </div>
                <label for="staff-avatar" title="{{ __('UserManagement::app.change_photo') }}" class="absolute bottom-0 right-0 grid size-7 cursor-pointer place-items-center rounded-full border-2 border-white bg-[#F26522] text-white shadow-sm transition hover:bg-[#d95318]">
                    <x-heroicon-o-camera class="size-3.5" />
                </label>
            </div>
            <div>
                <p class="text-sm font-semibold text-slate-800">{{ __('UserManagement::app.avatar') }}</p>
                <label for="staff-avatar" class="mt-1.5 inline-flex min-h-8 cursor-pointer items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-1 text-sm font-semibold text-slate-700 transition hover:border-[#F26522] hover:text-[#F26522]">
                    <x-heroicon-o-arrow-up-tray class="size-4" />
                    {{ __('UserManagement::app.upload_photo') }}
                </label>
                <input id="staff-avatar" name="avatar" type="file" accept="image/jpeg,image/png" class="sr-only" x-on:change="const file = $event.target.files[0]; if (file) { avatarPreview = URL.createObjectURL(file); avatarFileName = file.name; }">
                <p class="mt-1 text-xs text-slate-500">{{ __('UserManagement::app.photo_hint') }}</p>
                <p x-cloak x-show="avatarFileName" x-text="avatarFileName" class="mt-1 max-w-60 truncate text-xs font-medium text-slate-600"></p>
                @error('avatar')<span class="mt-1 block text-xs font-medium text-red-600">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:gap-3">
            @foreach(['name', 'phone'] as $field)
                <label class="text-sm font-semibold text-slate-700">
                    {{ __('UserManagement::app.fields.'.$field) }} <span class="text-red-600">*</span>
                    <input type="{{ $field === 'phone' ? 'tel' : 'text' }}" name="{{ $field }}" value="{{ old($field, $staff?->{$field}) }}" @required(true) @if($field === 'phone') inputmode="tel" autocomplete="tel" @endif maxlength="{{ $field === 'phone' ? 20 : 255 }}" @class([
                        'mt-1.5 block w-full rounded-lg border px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-[#F26522] focus:ring-2 focus:ring-orange-100',
                        'border-red-400' => $errors->has($field),
                        'border-slate-300' => ! $errors->has($field),
                    ])>
                    @error($field)<span class="mt-1 block text-xs font-medium text-red-600">{{ $message }}</span>@enderror
                </label>
            @endforeach

            <label class="text-sm font-semibold text-slate-700">
                {{ __('UserManagement::app.fields.date_of_birth') }} <span class="text-red-600">*</span>
                <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $staff?->date_of_birth?->format('Y-m-d')) }}" min="{{ now()->subYears(100)->toDateString() }}" max="{{ now()->subYears(16)->toDateString() }}" autocomplete="bday" required @class([
                    'mt-1.5 block w-full rounded-lg border px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-[#F26522] focus:ring-2 focus:ring-orange-100',
                    'border-red-400' => $errors->has('date_of_birth'),
                    'border-slate-300' => ! $errors->has('date_of_birth'),
                ])>
                @error('date_of_birth')<span class="mt-1 block text-xs font-medium text-red-600">{{ $message }}</span>@enderror
            </label>

            <label class="text-sm font-semibold text-slate-700">
                {{ __('UserManagement::app.fields.gender') }}
                <select name="gender" class="mt-1.5 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-[#F26522] focus:ring-2 focus:ring-orange-100">
                    <option value="">{{ __('UserManagement::app.gender_blank') }}</option>
                    @foreach(['male', 'female', 'other'] as $gender)
                        <option value="{{ $gender }}" @selected(old('gender', $staff?->gender) === $gender)>{{ __('UserManagement::app.gender_values.'.$gender) }}</option>
                    @endforeach
                </select>
                @error('gender')<span class="mt-1 block text-xs font-medium text-red-600">{{ $message }}</span>@enderror
            </label>

            <label class="text-sm font-semibold text-slate-700 sm:col-span-2">
                {{ __('UserManagement::app.fields.address') }}
                <input type="text" name="address" value="{{ old('address', $staff?->address) }}" maxlength="255" class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-[#F26522] focus:ring-2 focus:ring-orange-100">
                @error('address')<span class="mt-1 block text-xs font-medium text-red-600">{{ $message }}</span>@enderror
            </label>
        </div>
    </div>
</section>

<section class="rounded-xl border border-slate-200 bg-white">
    <div class="flex items-center gap-2 border-b border-slate-100 bg-slate-50 px-4 py-3">
        <x-heroicon-o-lock-closed class="size-5 text-slate-600" />
        <h2 class="text-sm font-bold text-slate-800">{{ __('UserManagement::app.account_info') }}</h2>
    </div>
    <div class="grid gap-4 p-4 sm:grid-cols-2 sm:p-5 xl:gap-3 xl:p-4">
        <label class="text-sm font-semibold text-slate-700">
            {{ __('UserManagement::app.fields.email') }} <span class="text-red-600">*</span>
            <input type="email" name="email" value="{{ old('email', $staff?->email) }}" required maxlength="255" autocomplete="email" @class([
                'mt-1.5 block w-full rounded-lg border px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-[#F26522] focus:ring-2 focus:ring-orange-100',
                'border-red-400' => $errors->has('email'),
                'border-slate-300' => ! $errors->has('email'),
            ])>
            @error('email')<span class="mt-1 block text-xs font-medium text-red-600">{{ $message }}</span>@enderror
        </label>

        <div x-data="{ passwordVisible: false }" class="text-sm font-semibold text-slate-700">
            <label for="staff-password">{{ __('UserManagement::app.fields.password') }} @if($isCreate)<span class="text-red-600">*</span>@endif</label>
            <div class="relative mt-1.5">
            <input id="staff-password" :type="passwordVisible ? 'text' : 'password'" name="password" @required($isCreate) autocomplete="new-password" class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 pr-11 text-sm text-slate-800 outline-none transition focus:border-[#F26522] focus:ring-2 focus:ring-orange-100">
            <button type="button" x-on:click="passwordVisible = !passwordVisible" :aria-label="passwordVisible ? @js(__('UserManagement::app.hide_password')) : @js(__('UserManagement::app.show_password'))" class="absolute inset-y-0 right-0 grid w-10 place-items-center text-slate-500 transition hover:text-[#F26522]">
                <x-heroicon-o-eye x-show="!passwordVisible" class="size-5" />
                <x-heroicon-o-eye-slash x-cloak x-show="passwordVisible" class="size-5" />
            </button>
            </div>
            @error('password')<span class="mt-1 block text-xs font-medium text-red-600">{{ $message }}</span>@enderror
            @unless($isCreate)<span class="mt-1 block text-xs font-medium text-slate-500">{{ __('UserManagement::app.password_hint') }}</span>@endunless
        </div>

        <div x-data="{ passwordVisible: false }" class="text-sm font-semibold text-slate-700 sm:col-span-2">
            <label for="staff-password-confirmation">{{ __('UserManagement::app.fields.password_confirmation') }} @if($isCreate)<span class="text-red-600">*</span>@endif</label>
            <div class="relative mt-1.5">
            <input id="staff-password-confirmation" :type="passwordVisible ? 'text' : 'password'" name="password_confirmation" @required($isCreate) autocomplete="new-password" @class([
                'block w-full rounded-lg border px-3 py-2.5 pr-11 text-sm text-slate-800 outline-none transition focus:border-[#F26522] focus:ring-2 focus:ring-orange-100',
                'border-red-400' => $errors->has('password_confirmation'),
                'border-slate-300' => ! $errors->has('password_confirmation'),
            ])>
            <button type="button" x-on:click="passwordVisible = !passwordVisible" :aria-label="passwordVisible ? @js(__('UserManagement::app.hide_password')) : @js(__('UserManagement::app.show_password'))" class="absolute inset-y-0 right-0 grid w-10 place-items-center text-slate-500 transition hover:text-[#F26522]">
                <x-heroicon-o-eye x-show="!passwordVisible" class="size-5" />
                <x-heroicon-o-eye-slash x-cloak x-show="passwordVisible" class="size-5" />
            </button>
            </div>
            @error('password_confirmation')<span class="mt-1 block text-xs font-medium text-red-600">{{ $message }}</span>@enderror
        </div>

        @unless($isCreate)
            <label class="flex items-center gap-3 text-sm font-semibold text-slate-700 sm:col-span-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $staff->is_active)) class="size-4 rounded border-slate-300 text-[#F26522] focus:ring-[#F26522]">
                {{ __('UserManagement::app.active_account') }}
            </label>
        @endunless
    </div>
</section>
</div>

<div class="mt-4 flex flex-wrap justify-end gap-3 border-t border-slate-100 pt-3">
    <a href="{{ route('staff-accounts.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50">{{ __('UserManagement::app.cancel') }}</a>
    <button type="submit" class="rounded-lg bg-[#F26522] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#d95318]">{{ __($isCreate ? 'UserManagement::app.save' : 'UserManagement::app.save_changes') }}</button>
</div>
