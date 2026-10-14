<template id="futa-ui-select-template" data-choose="{{ __('core::ui.choose') }}" data-no-options="{{ __('core::ui.no_options') }}">
    <div class="futa-control">
        <button type="button" data-ui-trigger class="futa-control__trigger" aria-haspopup="listbox">
            <span data-ui-value class="futa-control__value"></span>
            <x-heroicon-o-chevron-down class="futa-control__icon" aria-hidden="true" />
        </button>
        <div data-ui-panel class="futa-control-panel" role="listbox"></div>
        <p data-ui-error role="alert" class="futa-control__error" hidden></p>
    </div>
</template>
<template id="futa-ui-empty-options-template">
    <x-core::empty-state :title="__('core::ui.no_options')" compact />
</template>
