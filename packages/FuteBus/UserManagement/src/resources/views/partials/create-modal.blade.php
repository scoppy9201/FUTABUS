<dialog data-staff-create-dialog
    data-auto-open="{{ request()->routeIs('staff-accounts.create') || ($errors->any() && session()->hasOldInput()) ? '1' : '0' }}"
    aria-labelledby="staff-create-title"
    class="m-auto max-h-[calc(100dvh-2rem)] w-[min(46rem,calc(100vw-2rem))] overflow-auto rounded-2xl border border-slate-200 bg-white p-0 shadow-2xl backdrop:bg-slate-950/50">
    <form data-staff-wizard data-step="1" data-staff-live-validation
        data-initial-step="{{ $errors->hasAny(['name', 'phone', 'date_of_birth', 'gender', 'address', 'avatar']) ? 1 : ($errors->any() ? 2 : 1) }}"
        data-validation="{{ json_encode(__('UserManagement::app.validation')) }}"
        method="post" action="{{ route('staff-accounts.store') }}" enctype="multipart/form-data" novalidate
        x-data="{ avatarPreview: null, avatarFileName: '' }">
        @csrf
        <header class="sticky top-0 z-10 flex items-start justify-between gap-4 border-b border-slate-100 bg-white px-5 py-4 sm:px-6">
            <div>
                <h2 id="staff-create-title" class="text-xl font-extrabold text-slate-950">{{ __('UserManagement::app.create_account') }}</h2>
                <p class="mt-1 text-sm leading-6 text-slate-500">{{ __('UserManagement::app.create_description', ['company' => $company->name]) }}</p>
            </div>
            <button type="button" data-staff-create-close aria-label="{{ __('UserManagement::app.cancel') }}"
                class="rounded-full bg-slate-100 p-2 text-slate-500 hover:bg-slate-200"><x-heroicon-o-x-mark class="size-5" /></button>
        </header>
        <div class="p-5 sm:p-6">
            <ol class="mb-6 flex items-start" aria-label="{{ __('UserManagement::app.wizard.progress') }}">
                @foreach(['personal', 'account', 'review'] as $key)
                    <li data-step-marker="{{ $loop->iteration }}" class="relative flex-1 text-center text-xs font-semibold text-slate-500">
                        @unless($loop->last)<span data-step-connector aria-hidden="true" class="absolute left-1/2 top-4 w-full border-t-2 border-dashed border-slate-200"></span>@endunless
                        <span data-step-circle class="relative mx-auto mb-2 grid size-9 place-items-center rounded-full border-2 border-slate-200 bg-white text-sm">{{ $loop->iteration }}</span>
                        {{ __('UserManagement::app.wizard.'.$key) }}
                    </li>
                @endforeach
            </ol>
            @if($errors->any())
                <p role="alert" class="mb-4 rounded-xl bg-red-50 p-3 text-sm text-red-700">{{ __('UserManagement::app.wizard.fix_errors') }}</p>
            @endif
            @include('UserManagement::partials.form', ['staff' => null, 'isCreate' => true, 'wizard' => true])
            <section data-staff-step="3" hidden class="rounded-xl border border-slate-200 bg-slate-50 p-5">
                <h3 class="font-bold text-slate-950">{{ __('UserManagement::app.wizard.review_title') }}</h3>
                <p class="mt-2 text-sm leading-6 text-slate-500">{{ __('UserManagement::app.wizard.review_hint') }}</p>
                <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                    @foreach(['name', 'phone', 'date_of_birth', 'gender', 'address', 'email'] as $field)
                        <div class="min-w-0">
                            <dt class="text-xs text-slate-500">{{ __('UserManagement::app.fields.'.$field) }}</dt>
                            <dd data-staff-summary="{{ $field }}" class="mt-1 wrap-break-word text-sm font-semibold text-slate-800"></dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        </div>
        <footer class="sticky bottom-0 flex items-center justify-end gap-3 border-t border-slate-100 bg-white px-5 py-4 sm:px-6">
            <button type="button" data-staff-back class="mr-auto rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50">{{ __('UserManagement::app.wizard.previous') }}</button>
            <button type="button" data-staff-next class="inline-flex items-center gap-2 rounded-xl bg-futa-orange px-5 py-2.5 text-sm font-bold text-white hover:bg-futa-orange-dark">
                {{ __('UserManagement::app.wizard.next') }}<x-heroicon-o-arrow-right class="size-4" />
            </button>
            <button type="submit" data-staff-submit hidden class="rounded-xl bg-futa-orange px-5 py-2.5 text-sm font-bold text-white hover:bg-futa-orange-dark">{{ __('UserManagement::app.save') }}</button>
        </footer>
    </form>
</dialog>
