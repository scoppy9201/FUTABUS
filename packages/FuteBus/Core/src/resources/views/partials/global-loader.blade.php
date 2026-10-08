<div
    id="global-page-loader"
    class="global-page-loader"
    data-animation-url="{{ asset('icons/Bus booking loader.json') }}?v={{ filemtime(public_path('icons/Bus booking loader.json')) }}"
    role="status"
    aria-live="polite"
    aria-hidden="true"
>
    <div class="global-page-loader__card">
        <div class="global-page-loader__visual" aria-hidden="true">
            <div class="global-page-loader__animation"></div>
            <div class="global-page-loader__fallback"></div>
        </div>
        <p class="global-page-loader__label">{{ __('core::app.loading') }}</p>
    </div>
</div>
