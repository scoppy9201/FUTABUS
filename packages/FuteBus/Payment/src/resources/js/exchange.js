import { ticketExchangeWizard } from './exchange-wizard.js';

window.ticketExchangeWizard = ticketExchangeWizard;
const dialog = document.querySelector('[data-exchange-dialog]');
if (dialog) {
    let loading = false;
    document.addEventListener('click', async event => {
        const link = event.target.closest('[data-ticket-exchange]');
        if (!link) return;
        event.preventDefault();
        if (loading) return;
        loading = true;
        link.closest('[popover]')?.hidePopover();
        window.FutaLoader?.show(0);
        try {
            const response = await fetch(link.href, { credentials: 'same-origin', cache: 'no-store', signal: AbortSignal.timeout(15000) });
            if (!response.ok || response.redirected) throw new Error();
            dialog.querySelector('[data-exchange-content]').innerHTML = await response.text();
            dialog.showModal();
        } catch { window.FutaNotify?.error(dialog.dataset.error); }
        finally { loading = false; window.FutaLoader?.hide(); }
    });
    dialog.addEventListener('cancel', event => {
        event.preventDefault();
        const component = dialog.querySelector('[data-exchange-wizard]');
        if (component) window.Alpine.$data(component).close();
    });
    window.addEventListener('pagehide', () => {
        const component = dialog.querySelector('[data-exchange-wizard]');
        if (component) window.Alpine.$data(component).abandon();
    });
}
