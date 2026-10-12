export function moveFeedbackStep(current, count, direction, validate) {
    if (direction < 0) return Math.max(0, current - 1);
    if (current >= count - 1) return current;
    return validate(current) ? current + 1 : current;
}

export function initializeFeedbackWizard(form) {
    if (!form || form.dataset.wizardReady === 'true') return;
    const steps = [...form.querySelectorAll('[data-feedback-step]')];
    const back = form.querySelector('[data-feedback-step-back]');
    const next = form.querySelector('[data-feedback-step-next]');
    const submit = form.querySelector('[data-feedback-step-submit]');
    const progress = form.querySelector('[data-feedback-wizard-progress]');
    const viewport = form.querySelector('[data-feedback-wizard-viewport]');
    const status = form.querySelector('[data-feedback-step-status]');
    if (!steps.length || !back || !next || !submit || !progress || !viewport || !status) return;

    let current = 0;
    const indicators = [...form.querySelectorAll('[data-feedback-step-indicator]')];
    const focusField = (field) => {
        const trigger = field.closest('.futa-control')?.querySelector('[data-ui-trigger]');
        (trigger || field).focus({ preventScroll: true });
        (trigger || field).scrollIntoView({ block: 'nearest' });
    };
    const showStep = (index, focusHeading = false) => {
        current = index;
        steps.forEach((step, position) => { step.hidden = position !== current; });
        back.hidden = current === 0;
        next.hidden = current === steps.length - 1;
        submit.hidden = current !== steps.length - 1;
        indicators.forEach((indicator, position) => {
            if (position === current) indicator.setAttribute('aria-current', 'step');
            else indicator.removeAttribute('aria-current');
            const number = indicator.querySelector('[data-feedback-step-number]');
            const label = indicator.querySelector('[data-feedback-step-label]');
            const connector = indicator.querySelector('[data-feedback-step-connector]');
            number.classList.toggle('border-futa-green/25', position > current);
            number.classList.toggle('bg-futa-green/5', position > current);
            number.classList.toggle('text-futa-green', position > current);
            number.classList.toggle('border-futa-green', position <= current);
            number.classList.toggle('bg-futa-green', position <= current);
            number.classList.toggle('text-white', position <= current);
            number.classList.toggle('ring-2', position === current);
            number.classList.toggle('ring-futa-green/20', position === current);
            number.classList.toggle('ring-offset-2', position === current);
            label.classList.toggle('text-slate-500', position > current);
            label.classList.toggle('text-futa-green', position <= current);
            connector?.classList.toggle('border-futa-green', position < current);
            connector?.classList.toggle('border-futa-green/25', position >= current);
        });
        status.textContent = status.dataset.template.replace(':current', String(current + 1))
            .replace(':total', String(steps.length));
        viewport.scrollTop = 0;
        if (focusHeading) steps[current].querySelector('[data-feedback-step-heading]')?.focus({ preventScroll: true });
    };
    const firstInvalid = (scope) => [...scope.querySelectorAll('input, select, textarea')]
        .find((field) => field.willValidate && !field.validity.valid);
    const revealInvalid = (field) => {
        const index = steps.indexOf(field.closest('[data-feedback-step]'));
        if (index !== -1) showStep(index);
    };
    const validate = (index) => {
        const invalid = firstInvalid(steps[index]);
        if (!invalid) return true;
        revealInvalid(invalid);
        focusField(invalid);
        invalid.reportValidity();
        return false;
    };
    const move = (direction) => {
        const destination = moveFeedbackStep(current, steps.length, direction, validate);
        if (destination !== current) showStep(destination, true);
    };

    form.noValidate = true;
    form.dataset.wizardReady = 'true';
    viewport.classList.remove('space-y-6');
    viewport.classList.add('max-h-[max(8rem,min(26rem,calc(100dvh-17rem)))]');
    progress.hidden = false;
    back.addEventListener('click', () => move(-1));
    next.addEventListener('click', () => move(1));
    form.addEventListener('invalid', (event) => {
        revealInvalid(event.target);
    }, true);
    form.addEventListener('submit', (event) => {
        if (event.defaultPrevented) return;
        if (current !== steps.length - 1) {
            event.preventDefault();
            move(1);
            return;
        }
        const invalid = firstInvalid(form);
        if (invalid) {
            event.preventDefault();
            revealInvalid(invalid);
            focusField(invalid);
            invalid.reportValidity();
        }
    }, true);
    const serverInvalid = form.querySelector('[aria-invalid="true"]');
    const initial = serverInvalid ? steps.indexOf(serverInvalid.closest('[data-feedback-step]')) : 0;
    showStep(initial < 0 ? 0 : initial);
}

if (typeof document !== 'undefined') {
    document.querySelectorAll('[data-feedback-wizard]').forEach(initializeFeedbackWizard);
}
