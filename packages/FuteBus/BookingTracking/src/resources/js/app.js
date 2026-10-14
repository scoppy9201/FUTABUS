import { createDetailLoader } from './detail-loader';

function initBookingMonitor() {
    const root = document.querySelector('[data-booking-monitor]');
    if (!root || root.dataset.ready) return;
    root.dataset.ready = 'true';
    const panel = root.querySelector('#booking-monitor-detail');
    const content = panel.querySelector('[data-booking-content]');
    const loading = panel.querySelector('[data-booking-loading]');
    const error = panel.querySelector('[data-booking-error]');
    let url = null;
    const loader = createDetailLoader(window.fetch.bind(window), ({ state, html }) => {
        loading.hidden = state !== 'loading';
        error.hidden = state !== 'error';
        content.setAttribute('aria-busy', String(state === 'loading'));
        content.innerHTML = state === 'ready' ? html : '';
    });
    root.addEventListener('click', event => {
        const link = event.target.closest('[data-booking-detail]');
        if (!link || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        url = link.href;
        panel.showModal();
        loader.load(url);
    });
    panel.addEventListener('click', event => { if (event.target === panel) panel.close(); });
    panel.addEventListener('close', () => { loader.cancel(); content.innerHTML = ''; });
    panel.querySelector('[data-booking-retry]').addEventListener('click', () => { if (url) loader.load(url); });
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initBookingMonitor, { once: true });
else initBookingMonitor();
