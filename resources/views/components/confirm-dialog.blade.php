<dialog
    id="global-confirm-dialog"
    aria-labelledby="global-confirm-title"
    aria-describedby="global-confirm-message"
    data-default-title="{{ __('core::confirm.title') }}"
    data-default-message="{{ __('core::confirm.message') }}"
    data-default-confirm="{{ __('core::confirm.confirm') }}"
    class="m-auto max-h-[calc(100dvh-2rem)] w-[calc(100%-2rem)] max-w-md origin-top overflow-hidden rounded-2xl border border-gray-100 bg-white p-0 text-gray-900 shadow-2xl backdrop:bg-gray-950/55"
>
    <div class="max-h-[calc(100dvh-2rem)] overflow-y-auto overscroll-contain p-6 sm:p-7">
        <div class="flex items-start gap-4">
            <span data-dialog-warning-icon class="grid size-11 shrink-0 place-items-center rounded-full bg-amber-50 text-amber-500">
                <x-heroicon-o-exclamation-triangle class="size-6" />
            </span>
            <span data-dialog-info-icon class="hidden size-11 shrink-0 place-items-center rounded-full bg-sky-50 text-sky-600">
                <x-heroicon-o-information-circle class="size-6" />
            </span>
            <div class="min-w-0 pt-0.5">
                <h2 id="global-confirm-title" class="text-lg font-bold leading-snug"></h2>
                <p id="global-confirm-message" class="mt-2 text-sm leading-6 text-gray-600"></p>
            </div>
        </div>

        <div class="mt-7 flex justify-end gap-3">
            <button
                type="button"
                data-confirm-cancel
                class="min-w-22 rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-800 transition hover:bg-gray-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#ef5222]"
            >
                {{ __('core::confirm.cancel') }}
            </button>
            <button
                type="button"
                data-confirm-accept
                class="min-w-26 rounded-lg bg-[#ef5222] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#d94316] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#ef5222]"
            ></button>
        </div>
    </div>
</dialog>
