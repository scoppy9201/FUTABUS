document.querySelectorAll('[data-auth-email-field]').forEach((field) => {
    const input = field.querySelector('[data-auth-email-input]');
    const error = field.querySelector('.auth-email-error');
    const label = field.querySelector('.auth-email-field');
    if (!(input instanceof HTMLInputElement) || !error || !label) return;

    const validEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    let touched = input.value.length > 0;

    function validate() {
        const value = input.value.trim();
        const message = !value ? error.dataset.requiredMessage
            : value.length > 255 ? error.dataset.maxMessage
                : !validEmail.test(value) || input.validity.typeMismatch ? error.dataset.invalidMessage : '';
        const invalid = touched && Boolean(message);
        error.textContent = invalid ? message : '';
        error.hidden = !invalid;
        label.dataset.invalid = invalid ? 'true' : 'false';
        error.classList.toggle('hidden', !invalid);
        input.setAttribute('aria-invalid', invalid ? 'true' : 'false');
        return !message;
    }

    input.addEventListener('input', () => {
        touched = true;
        validate();
    });
    input.addEventListener('blur', () => {
        touched = true;
        validate();
    });
    input.addEventListener('invalid', (event) => {
        event.preventDefault();
        touched = true;
        validate();
    });
    input.form?.addEventListener('submit', (event) => {
        touched = true;
        if (!validate()) {
            event.preventDefault();
            input.focus();
        }
    });
    validate();
});
