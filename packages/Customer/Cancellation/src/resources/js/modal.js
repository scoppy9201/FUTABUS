const dialog = document.querySelector('[data-cancellation-dialog]');
if (dialog) {
    let request;
    let trigger;
    let completed = false;
    let busy = false;
    let step = 0;
    let pollTimer;
    const pollStatus = async () => {
        const current = form();
        if (!dialog.open || busy || !current || document.hidden) return;
        try {
            const response = await fetch(current.dataset.statusUrl, { credentials: 'same-origin', headers: { Accept: 'application/json' }, cache: 'no-store' });
            if (!response.ok || !dialog.open || form() !== current) return;
            const result = await response.json();
            const labels = JSON.parse(current.querySelector('[data-cancel-statuses]').textContent);
            for (const receipt of result.receipts) {
                current.querySelectorAll('[data-refund-status]').forEach((element) => {
                    if (element.dataset.refundStatus === receipt.reference) element.textContent = labels[receipt.status];
                });
            }
        } catch { /* Keep the last verified refund status until the next successful check. */ }
    };
    const money = (value) => `${new Intl.NumberFormat(document.documentElement.lang || 'vi').format(value)} đ`;
    const form = () => dialog.querySelector('[data-cancellation-form]');
    const error = (message = '') => { form().querySelector('[data-cancel-error]').textContent = message; };
    const advance = (value) => {
        step = value;
        const current = form();
        current.querySelectorAll('[data-cancel-step]').forEach((section) => { section.hidden = Number(section.dataset.cancelStep) !== step; });
        current.querySelectorAll('[data-cancel-progress]').forEach((item) => {
            const active = Number(item.dataset.cancelProgress) <= step;
            const complete = Number(item.dataset.cancelProgress) < step;
            item.classList.toggle('text-futa-green', active);
            item.classList.toggle('text-slate-500', !active);
            item.dataset.complete = String(complete);
            item.querySelector('[data-cancel-number]').hidden = complete;
            item.querySelector('[data-cancel-check]').classList.toggle('hidden', !complete);
            item.querySelector('[data-cancel-circle]').classList.toggle('bg-futa-green', complete);
            item.querySelector('[data-cancel-circle]').classList.toggle('text-white', complete);
            if (Number(item.dataset.cancelProgress) === step) item.setAttribute('aria-current', 'step');
            else item.removeAttribute('aria-current');
        });
        current.querySelector('[data-cancel-back]').hidden = step === 0;
        current.querySelector('[data-cancel-next]').hidden = step === 2;
        current.querySelector('[data-cancel-next]').disabled = step === 0
            ? !current.querySelector('[name="ticket_id"]:checked:not(:disabled)') : !current.elements.reason.value;
        current.querySelector('[data-cancel-submit]').hidden = step !== 2;
        current.querySelector('[data-cancel-nav]').hidden = step === 3;
        dialog.scrollTop = 0;
        const heading = current.querySelector(`[data-cancel-step="${step}"] h3`);
        heading?.setAttribute('tabindex', '-1');
        if (dialog.open) heading?.focus({ preventScroll: true });
    };
    const send = async (url, data) => {
        request = new AbortController();
        const timer = setTimeout(() => request.abort(), 15000);
        try {
            const response = await fetch(url, { method: 'POST', credentials: 'same-origin',
                headers: { Accept: 'application/json' }, body: data, signal: request.signal });
            const result = await response.json();
            if (!response.ok) throw new Error(Object.values(result.errors || {})[0]?.[0] || dialog.dataset.error);
            return result;
        } finally { clearTimeout(timer); }
    };
    document.addEventListener('click', async (event) => {
        const link = event.target.closest('[data-ticket-cancel]');
        if (!link) return;
        event.preventDefault();
        if (busy) return;
        link.closest('[popover]')?.hidePopover();
        trigger = link;
        busy = true;
        completed = false;
        window.FutaLoader?.show(0);
        try {
            const response = await fetch(link.href, { credentials: 'same-origin', headers: { Accept: 'text/html' } });
            if (!response.ok) throw new Error(dialog.dataset.error);
            dialog.querySelector('[data-cancellation-content]').innerHTML = await response.text();
            advance(0);
            dialog.showModal();
            clearInterval(pollTimer);
            pollTimer = setInterval(pollStatus, 10000);
            form().querySelector('[data-cancellation-close]').focus();
        } catch { window.FutaNotify?.error(dialog.dataset.error); }
        finally { busy = false; window.FutaLoader?.hide(); }
    });
    dialog.addEventListener('click', async (event) => {
        if (event.target.closest('[data-cancel-refresh]')) return window.location.reload();
        if ((event.target === dialog || event.target.closest('[data-cancellation-close]')) && !busy) return dialog.close();
        if (event.target.closest('[data-cancel-back]') && !busy) { error(); advance(step - 1); return; }
        if (!event.target.closest('[data-cancel-next]') || busy) return;
        const current = form();
        error();
        if (step === 1) {
            if (!current.elements.reason.value) { current.querySelector('[name="reason"]').focus(); return; }
            current.querySelector('[data-cancel-reason]').textContent = current.querySelector('[name="reason"]:checked').closest('label').textContent.trim();
            current.elements.consent.checked = false;
            current.querySelector('[data-cancel-submit]').disabled = true;
            advance(2); return;
        }
        if (!current.querySelector('[name="ticket_id"]:checked:not(:disabled)')) { current.querySelector('[name="ticket_id"]:not(:disabled)')?.focus(); return; }
        busy = true;
        current.inert = true;
        window.FutaLoader?.show(0);
        try {
            const result = await send(current.dataset.quoteUrl, new FormData(current));
            current.elements.expected_refund.value = result.quote.refund;
            current.querySelector('[data-cancel-route]').textContent = `${result.ticket.origin_city} → ${result.ticket.destination_city}`;
            current.querySelector('[data-cancel-seat]').textContent = `${result.ticket.departure_time} · ${result.ticket.seat_code || ''}`;
            for (const key of ['amount', 'fee', 'refund']) current.querySelector(`[data-cancel-${key}]`).textContent = money(result.quote[key]);
            current.querySelector('[data-cancel-percent]').textContent = `(${result.quote.fee_percent}%)`;
            advance(1);
        } catch (failure) { error(failure.message || dialog.dataset.error); }
        finally { busy = false; current.inert = false; window.FutaLoader?.hide(); }
    });
    dialog.addEventListener('change', (event) => {
        if (event.target.name === 'consent') form().querySelector('[data-cancel-submit]').disabled = !event.target.checked;
        if (event.target.name === 'ticket_id' || event.target.name === 'reason') {
            form().querySelector('[data-cancel-next]').disabled = step === 0
                ? !form().querySelector('[name="ticket_id"]:checked:not(:disabled)') : !form().elements.reason.value;
        }
    });
    dialog.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (busy || step !== 2 || !form().elements.consent.checked) return;
        busy = true;
        const current = form();
        current.inert = true;
        current.querySelector('[data-cancel-submit]').disabled = true;
        window.FutaLoader?.show(0);
        try {
            const result = await send(current.action, new FormData(current));
            completed = true;
            current.querySelector('[data-cancel-reference]').textContent = result.reference;
            current.querySelector('[data-cancel-status]').dataset.refundStatus = result.reference;
            current.querySelector('[data-cancel-status]').textContent = JSON.parse(current.querySelector('[data-cancel-statuses]').textContent)[result.refund_status];
            advance(3);
        } catch (failure) { error(failure.message || dialog.dataset.error); }
        finally { busy = false; current.inert = false; current.querySelector('[data-cancel-submit]').disabled = !current.elements.consent.checked; window.FutaLoader?.hide(); }
    });
    dialog.addEventListener('cancel', (event) => { if (busy) event.preventDefault(); });
    dialog.addEventListener('close', () => { clearInterval(pollTimer); if (completed) window.location.reload(); else trigger?.closest('tr')?.querySelector('[data-ticket-history-actions]')?.focus(); });
}
