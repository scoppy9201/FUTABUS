function initializeStaffWizard() {
    const dialog = document.querySelector('[data-staff-create-dialog]');
    if (!dialog) return;
    const form = dialog.querySelector('[data-staff-wizard]');
    const panels = [...form.querySelectorAll('[data-staff-step]')];
    const next = form.querySelector('[data-staff-next]');
    const back = form.querySelector('[data-staff-back]');
    const submit = form.querySelector('[data-staff-submit]');
    let step = Number(form.dataset.initialStep);
    let sending = false;
    let opener;
    const personal = ['avatar', 'name', 'phone', 'date_of_birth', 'gender', 'address'];
    const account = ['email', 'password', 'password_confirmation'];
    const focusField = field => (field.closest('.futa-control')?.querySelector('[data-ui-trigger]') || field).focus();

    function showStep(value, focus = true) {
        step = value;
        form.dataset.step = String(step);
        panels.forEach(panel => { panel.hidden = Number(panel.dataset.staffStep) !== step; });
        next.hidden = step === 3;
        next.classList.toggle('inline-flex', step !== 3);
        submit.hidden = step !== 3;
        back.hidden = step === 1;
        form.querySelectorAll('[data-step-marker]').forEach(marker => {
            const number = Number(marker.dataset.stepMarker);
            const circle = marker.querySelector('[data-step-circle]');
            marker.classList.toggle('text-futa-green', number <= step);
            marker.classList.toggle('text-slate-500', number > step);
            circle.classList.toggle('border-futa-green', number <= step);
            circle.classList.toggle('border-slate-200', number > step);
            circle.classList.toggle('bg-futa-green', number < step);
            circle.classList.toggle('bg-white', number >= step);
            circle.classList.toggle('text-white', number < step);
            const connector = marker.querySelector('[data-step-connector]');
            connector?.classList.toggle('border-futa-green', number < step);
            connector?.classList.toggle('border-slate-200', number >= step);
            if (number === step) marker.setAttribute('aria-current', 'step');
            else marker.removeAttribute('aria-current');
        });
        if (step === 3) {
            form.querySelectorAll('[data-staff-summary]').forEach(target => {
                const source = form.elements.namedItem(target.dataset.staffSummary);
                target.textContent = (source.tagName === 'SELECT' ? source.selectedOptions[0]?.textContent : source.value) || '—';
            });
        }
        if (focus) {
            const field = panels[step - 1].querySelector('input:not([type=file]), select');
            if (field) focusField(field);
            else submit.focus();
        }
    }
    function advance() {
        if (sending || step === 3) return;
        const invalid = form.staffValidation(step === 1 ? personal : account);
        if (invalid.length) { focusField(invalid[0]); return; }
        showStep(step + 1);
    }
    showStep(step, false);
    next.addEventListener('click', advance);
    form.addEventListener('staff-next', advance);
    back.addEventListener('click', () => { if (!sending) showStep(Math.max(1, step - 1)); });
    form.addEventListener('staff-invalid', event => showStep(personal.includes(event.detail) ? 1 : 2, false));
    form.addEventListener('submit', event => {
        if (event.defaultPrevented) return;
        if (sending || step !== 3) { event.preventDefault(); return; }
        sending = true;
        // Keep fields enabled so the browser includes them in the multipart submission.
        next.disabled = back.disabled = submit.disabled = true;
    });
    document.querySelectorAll('[data-staff-create]').forEach(link => link.addEventListener('click', event => {
        if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        opener = link;
        dialog.showModal();
        showStep(step);
    }));
    dialog.addEventListener('click', event => {
        if (!sending && (event.target === dialog || event.target.closest('[data-staff-create-close]'))) dialog.close();
    });
    dialog.addEventListener('cancel', event => { if (sending) event.preventDefault(); });
    dialog.addEventListener('close', () => opener?.focus());
    if (dialog.dataset.autoOpen === '1') { dialog.showModal(); showStep(step); }
    window.addEventListener('pageshow', () => {
        sending = false;
        next.disabled = back.disabled = submit.disabled = false;
    });
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initializeStaffWizard);
else initializeStaffWizard();
