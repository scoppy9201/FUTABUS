const workspace = document.querySelector('[data-refund-workspace]');
if (workspace) {
    const content = workspace.querySelector('[data-refund-content]');
    let busy = false;
    let selectedId = workspace.querySelector('[data-refund-detail][aria-current]')?.dataset.refundId;

    workspace.addEventListener('click', async (event) => {
        if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        const link = event.target.closest('[data-refund-detail]')
            || event.target.closest('[data-refund-row]')?.querySelector('[data-refund-detail]');
        if (!link) return;
        event.preventDefault();
        if (busy) return;
        busy = true;
        content.setAttribute('aria-busy', 'true');
        content.inert = true;
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 15000);
        window.FutaLoader?.show(0);
        try {
            const response = await fetch(link.href, { credentials: 'same-origin', signal: controller.signal, cache: 'no-store' });
            if (!response.ok) throw new Error(workspace.dataset.error);
            content.innerHTML = await response.text();
            selectedId = link.dataset.refundId;
            workspace.querySelectorAll('[data-refund-row]').forEach((row) => {
                const selected = row.dataset.refundRow === selectedId;
                row.classList.toggle('bg-futa-orange-soft/50', selected);
                const anchor = row.querySelector('[data-refund-detail]');
                if (selected) anchor.setAttribute('aria-current', 'true');
                else anchor.removeAttribute('aria-current');
            });
            content.inert = false;
            content.querySelector('[data-refund-heading]')?.focus({ preventScroll: true });
            if (window.matchMedia('(max-width: 1279px)').matches) content.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } catch { window.FutaNotify?.error(workspace.dataset.error); }
        finally {
            clearTimeout(timeout);
            busy = false;
            content.inert = false;
            content.setAttribute('aria-busy', 'false');
            window.FutaLoader?.hide();
        }
    });
    workspace.addEventListener('submit', async (event) => {
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
        content.setAttribute('aria-busy', 'true');
        const errorPanel = content.querySelector('[data-refund-error]');
        errorPanel.textContent = '';
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 15000);
        window.FutaLoader?.show(0);
        try {
            const response = await fetch(form.action, { method: 'POST', credentials: 'same-origin',
                headers: { Accept: 'application/json' }, body: data, signal: controller.signal });
            const result = await response.json();
            if (!response.ok) throw new Error(Object.values(result.errors || {})[0]?.[0] || result.message || workspace.dataset.error);
            window.FutaNotify?.success(workspace.dataset.saved);
            // Refresh the list, totals, audit history and version together after a state transition.
            const destination = new URL(window.location.href);
            destination.searchParams.set('selected', selectedId);
            destination.searchParams.delete('export');
            window.location.assign(destination.href);
        } catch (error) {
            errorPanel.textContent = error.name === 'AbortError' ? workspace.dataset.error : (error.message || workspace.dataset.error);
        } finally {
            clearTimeout(timeout);
            busy = false;
            content.inert = false;
            content.setAttribute('aria-busy', 'false');
            window.FutaLoader?.hide();
        }
    });
}
