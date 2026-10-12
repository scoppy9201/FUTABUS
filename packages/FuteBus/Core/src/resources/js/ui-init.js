import { initializeUiSelects } from './ui-select.js';
import { initializeUiDatePickers } from './ui-date-picker.js';
import { createPopup } from './ui-popup.js';
import './ui-overlays.js';

window.FutaUi = { createPopup, initializeUiSelects, initializeUiDatePickers };
document.dispatchEvent(new Event('futa-ui-ready'));

function initializeControls() {
    initializeUiSelects();
    initializeUiDatePickers();
    document.querySelectorAll('[role="listbox"], [role="menu"], #account-menu, .hero-location-results, .booking-page__stop-menu, [data-ui-menu]')
        .forEach((panel) => panel.classList.add('futa-dropdown-menu'));
}

function startControls() {
    initializeControls();
    let scheduled = false;
    const observer = new MutationObserver(() => {
        if (scheduled) return;
        scheduled = true;
        requestAnimationFrame(() => {
            scheduled = false;
            initializeControls();
        });
    });
    observer.observe(document.body, { childList: true, subtree: true, attributes: true, attributeFilter: ['type'] });
    window.addEventListener('pageshow', initializeControls);
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', startControls, { once: true });
else startControls();
