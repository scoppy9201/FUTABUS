function initializeAccessModals() {
    document.querySelectorAll('[data-access-modal]').forEach(dialog => {
        let opener;
        let sending = false;
        const form = dialog.querySelector('form');
        const close = () => {
            if (sending) return;
            if (dialog.dataset.closeUrl) {
                window.location.assign(dialog.dataset.closeUrl);
                return;
            }
            dialog.close();
        };
        const open = trigger => {
            opener = trigger;
            dialog.showModal();
            dialog.querySelector('[data-role-step]:not([hidden]) input:not([type=hidden])')?.focus();
        };
        document.querySelectorAll(`[data-access-target="${dialog.id}"]`).forEach(trigger => {
            trigger.addEventListener('click', () => open(trigger));
        });
        if (dialog.hasAttribute('data-role-wizard-modal')) {
            initializeRoleWizard(form);
            document.querySelectorAll('[data-role-create]').forEach(trigger => trigger.addEventListener('click', event => {
                if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
                event.preventDefault();
                open(trigger);
            }));
        }
        dialog.addEventListener('click', event => {
            if (event.target === dialog || event.target.closest('[data-access-close]')) close();
        });
        dialog.addEventListener('cancel', event => {
            event.preventDefault();
            close();
        });
        dialog.addEventListener('close', () => opener?.focus());
        form.addEventListener('submit', event => {
            if (event.defaultPrevented) return;
            if (sending) { event.preventDefault(); return; }
            sending = true;
            form.querySelectorAll('button').forEach(button => { button.disabled = true; });
        });
        window.addEventListener('pageshow', () => {
            sending = false;
            form.querySelectorAll('button').forEach(button => { button.disabled = button.hasAttribute('data-permanent-disabled'); });
        });
        if (dialog.dataset.autoOpen === '1') open();
    });
}

function initializeRoleWizard(form) {
    const panels = [...form.querySelectorAll('[data-role-step]')];
    const next = form.querySelector('[data-role-next]');
    const back = form.querySelector('[data-role-back]');
    const submit = form.querySelector('[data-role-submit]');
    const fields = [...form.querySelectorAll('input[name="name"], textarea[name="description"]')];
    const permissions = [...form.querySelectorAll('[data-role-permission]')];
    let step = Number(form.dataset.initialStep) || 1;
    function show(value, focus = true) {
        step = value;
        panels.forEach(panel => { panel.hidden = Number(panel.dataset.roleStep) !== step; });
        next.hidden = step === 3;
        submit.hidden = step !== 3;
        back.hidden = step === 1;
        form.querySelectorAll('[data-role-marker]').forEach(marker => {
            const number = Number(marker.dataset.roleMarker);
            marker.classList.toggle('text-futa-green', number <= step);
            marker.classList.toggle('text-slate-500', number > step);
            const circle = marker.querySelector('[data-role-circle]');
            circle.classList.toggle('border-futa-green', number <= step);
            circle.classList.toggle('border-slate-200', number > step);
            circle.classList.toggle('bg-futa-green', number < step);
            circle.classList.toggle('bg-white', number >= step);
            circle.classList.toggle('text-white', number < step);
            const connector = marker.querySelector('[data-role-connector]');
            connector?.classList.toggle('border-futa-green', number < step);
            connector?.classList.toggle('border-slate-200', number >= step);
            if (number === step) marker.setAttribute('aria-current', 'step');
            else marker.removeAttribute('aria-current');
        });
        if (step === 3) {
            form.querySelector('[data-role-summary-name]').textContent = form.elements.name.value.trim();
            form.querySelector('[data-role-summary-description]').textContent = form.elements.description.value.trim() || '—';
            const selected = permissions.filter(input => input.checked);
            form.querySelector('[data-role-count]').textContent = selected.length;
            form.querySelector('[data-role-zero]').hidden = selected.length > 0;
            const list = form.querySelector('[data-role-summary-permissions]');
            list.replaceChildren(...selected.map(input => {
                const item = document.createElement('li');
                item.textContent = input.closest('label').querySelector('[data-role-permission-label]').textContent.trim();
                return item;
            }));
        }
        if (focus) {
            const target = step === 3 ? submit : panels[step - 1].querySelector('input:not([type=hidden]), textarea');
            target?.focus();
        }
    }
    function validateField(field) {
        const value = field.value.trim();
        const message = field.required && !value ? field.dataset.requiredMessage
            : field.maxLength > 0 && [...value].length > field.maxLength ? field.dataset.maxMessage : '';
        field.closest('label').querySelector('[data-role-field-error]').textContent = message;
        field.setAttribute('aria-invalid', message ? 'true' : 'false');
        return !message;
    }
    fields.forEach(field => {
        ['input', 'blur'].forEach(event => field.addEventListener(event, () => validateField(field)));
    });
    function valid() {
        form.elements.name.value = form.elements.name.value.trim();
        const invalid = fields.filter(field => !validateField(field))[0];
        if (!invalid) return true;
        show(1);
        invalid.focus();
        return false;
    }
    next.addEventListener('click', () => { if (valid()) show(Math.min(3, step + 1)); });
    back.addEventListener('click', () => show(Math.max(1, step - 1)));
    form.addEventListener('submit', event => {
        if (step !== 3) { event.preventDefault(); if (valid()) show(step + 1); }
        else if (!valid()) event.preventDefault();
    });
    form.querySelectorAll('[data-role-group-toggle]').forEach(button => button.addEventListener('click', () => {
        const inputs = [...button.closest('fieldset').querySelectorAll('[data-role-permission]')];
        const checked = !inputs.every(input => input.checked);
        inputs.forEach(input => { input.checked = checked; });
    }));
    form.querySelector('[data-role-permission-search]').addEventListener('input', event => {
        const query = event.target.value.trim().toLocaleLowerCase();
        let matches = 0;
        form.querySelectorAll('[data-role-permission-group]').forEach(group => {
            const headingMatches = group.querySelector('h3').textContent.toLocaleLowerCase().includes(query);
            const items = [...group.querySelectorAll('[data-role-permission-item]')];
            items.forEach(item => { item.hidden = !headingMatches && !item.textContent.toLocaleLowerCase().includes(query); });
            group.hidden = !items.some(item => !item.hidden);
            if (!group.hidden) matches++;
        });
        form.querySelector('[data-role-no-results]').hidden = matches > 0;
    });
    show(step, false);
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initializeAccessModals);
else initializeAccessModals();
