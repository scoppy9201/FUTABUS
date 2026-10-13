const dialog = document.querySelector('[data-refund-dialog]');
if (dialog) {
    const content = dialog.querySelector('[data-refund-content]');
    let url;
    let trigger;
    let busy = false;
    let changed = false;
    async function load(signal) {
        const response = await fetch(url, { credentials: 'same-origin', signal, cache: 'no-store' });
        if (!response.ok) throw new Error(dialog.dataset.error);
        content.innerHTML = await response.text();
    }
    document.addEventListener('click', async (event) => {
        const link = event.target.closest('[data-refund-detail]');
        if (!link) return;
        event.preventDefault();
        if (busy) return;
        busy = true;
        changed = false;
        trigger = link;
        url = link.href;
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 15000);
        window.FutaLoader?.show(0);
        try {
            await load(controller.signal);
            dialog.showModal();
            content.querySelector('[data-refund-close]').focus();
        } catch { window.FutaNotify?.error(dialog.dataset.error); }
        finally { clearTimeout(timeout); busy = false; window.FutaLoader?.hide(); }
    });
    dialog.addEventListener('submit', async (event) => {
        const form = event.target.closest('[data-refund-form]');
        if (!form) return;
        event.preventDefault();
        if (busy) return;
        busy = true;
        const data = new FormData(form);
        if (data.get('action') === 'complete') {
            data.set('bank_paid_at', `${data.get('bank_paid_date')} ${data.get('bank_paid_hour')}:${data.get('bank_paid_minute')}:00`);
        }
        content.inert = true;
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 15000);
        window.FutaLoader?.show(0);
        try {
            const response = await fetch(form.action, { method: 'POST', credentials: 'same-origin',
                headers: { Accept: 'application/json' }, body: data, signal: controller.signal });
            const result = await response.json();
            if (!response.ok) throw new Error(Object.values(result.errors || {})[0]?.[0] || result.message || dialog.dataset.error);
            changed = true;
            await load(controller.signal);
            content.querySelector('[data-refund-close]').focus();
            window.FutaNotify?.success(dialog.dataset.saved);
        } catch (error) {
            content.querySelector('[data-refund-error]').textContent = error.message || dialog.dataset.error;
        } finally { clearTimeout(timeout); busy = false; content.inert = false; window.FutaLoader?.hide(); }
    });
    dialog.addEventListener('click', (event) => {
        if (!busy && (event.target === dialog || event.target.closest('[data-refund-close]'))) dialog.close();
    });
    dialog.addEventListener('cancel', (event) => { if (busy) event.preventDefault(); });
    dialog.addEventListener('close', () => { if (changed) window.location.reload(); else trigger?.focus(); });
}
