const workspace = document.querySelector('[data-refund-workspace]');
if (workspace) {
    const content = workspace.querySelector('[data-refund-content]');
    const panel = workspace.querySelector('#refund-details');
    let busy = false;
    let selectedId = workspace.querySelector('[data-refund-detail][aria-current]')?.dataset.refundId;

    const fieldKey = name => name.startsWith('bank_paid_') ? 'bank_paid_at' : name;
    const fields = form => [...form.querySelectorAll('input, select, textarea')];
    const showFieldError = (form, name, message) => {
        const error = [...form.querySelectorAll('[data-refund-field-error]')]
            .find(element => element.dataset.refundFieldError === fieldKey(name));
        if (!error) return false;
        error.textContent = message;
        error.hidden = !message;
        fields(form).filter(field => fieldKey(field.name) === fieldKey(name)).forEach(field => {
            field.setAttribute('aria-invalid', message ? 'true' : 'false');
            if (error.id) field.setAttribute('aria-describedby', error.id);
        });
        return true;
    };
    const clearErrors = form => {
        form.querySelectorAll('[data-refund-field-error]').forEach(error => {
            showFieldError(form, error.dataset.refundFieldError, '');
        });
    };
    for (const eventName of ['input', 'change']) {
        workspace.addEventListener(eventName, event => {
            const form = event.target.closest('[data-refund-form]');
            if (form && event.target.name) showFieldError(form, event.target.name, '');
        });
    }

    if (panel && workspace.dataset.refundOpen === 'true') panel.showModal();
    panel?.addEventListener('close', () => {
        workspace.querySelectorAll('[data-refund-row]').forEach((row) => {
            row.classList.remove('bg-futa-orange-soft/50');
            row.querySelectorAll('[data-refund-detail]').forEach((link) => link.removeAttribute('aria-current'));
        });
        const destination = new URL(window.location.href);
        destination.searchParams.delete('selected');
        window.history.replaceState(window.history.state, '', destination.href);
    });

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
            if (panel && !panel.open) panel.showModal();
            content.querySelector('[data-refund-heading]')?.focus({ preventScroll: true });
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
        clearErrors(form);
        const invalidFields = fields(form).filter(field => field.willValidate && !field.checkValidity());
        if (invalidFields.length) {
            invalidFields.forEach(field => showFieldError(form, field.name, field.validationMessage));
            invalidFields[0].focus();
            return;
        }
        busy = true;
        const confirmed = await window.FutaConfirm?.({
            title: form.dataset.confirmTitle,
            message: form.dataset.confirmMessage,
            label: form.dataset.confirmLabel,
        });
        if (confirmed !== true) { busy = false; return; }
        const data = new FormData(form);
        if (data.get('action') === 'complete') {
            data.set('bank_paid_at', `${data.get('bank_paid_date')} ${data.get('bank_paid_hour')}:${data.get('bank_paid_minute')}:00`);
        }
        content.inert = true;
        content.setAttribute('aria-busy', 'true');
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 15000);
        window.FutaLoader?.show(0);
        try {
            const response = await fetch(form.getAttribute('action'), { method: 'POST', credentials: 'same-origin',
                headers: { Accept: 'application/json' }, body: data, signal: controller.signal });
            const result = await response.json();
            if (!response.ok) {
                if (response.status === 422 && result.errors) {
                    const unplaced = [];
                    for (const [name, messages] of Object.entries(result.errors)) {
                        if (!showFieldError(form, name, messages[0])) unplaced.push(messages[0]);
                    }
                    if (unplaced.length) window.FutaNotify?.error(unplaced.join(' '));
                    return;
                }
                throw new Error(result.message || workspace.dataset.error);
            }
            window.FutaNotify?.success(workspace.dataset.saved);
            // Refresh the list, totals, audit history and version together after a state transition.
            const destination = new URL(window.location.href);
            destination.searchParams.set('selected', selectedId);
            destination.searchParams.delete('export');
            window.location.assign(destination.href);
        } catch (error) {
            window.FutaNotify?.error(error.name === 'AbortError' ? workspace.dataset.error : (error.message || workspace.dataset.error));
        } finally {
            clearTimeout(timeout);
            busy = false;
            content.inert = false;
            content.setAttribute('aria-busy', 'false');
            window.FutaLoader?.hide();
        }
    });
}
