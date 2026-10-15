<dialog data-exchange-dialog aria-label="{{ __('Payment::exchange.title') }}" data-error="{{ __('Payment::exchange.error') }}"
    class="m-auto max-h-[calc(100dvh-2rem)] w-[min(68rem,calc(100vw-1.5rem))] overflow-y-auto rounded-2xl border-0 bg-white p-0 text-slate-900 shadow-2xl backdrop:bg-slate-950/60">
    <div data-exchange-content></div>
</dialog>
@vite('packages/FuteBus/Payment/src/resources/js/app.js')
