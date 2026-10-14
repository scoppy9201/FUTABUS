function initializeStaffForms() {
    document.querySelectorAll('[data-staff-live-validation]').forEach(form => {
        const messages = JSON.parse(form.dataset.validation);
        const fields = [...form.querySelectorAll('input:not([type=hidden]):not([type=checkbox]), select')];
        const touched = new Set();
        const errors = new Map();
        const find = name => fields.find(field => field.name === name);

        function validate(field) {
            const value = field.value.trim();
            let message = '';
            if (field.required && !value) message = messages[field.name + '_required'] || messages.birth_date_required;
            if (value && field.name === 'phone' && !/^(?:0|\+84)[0-9]{9}$/.test(value.replace(/[\s.()-]+/g, ''))) message = messages.phone_invalid;
            if (value && field.name === 'email' && field.validity.typeMismatch) message = messages.email_invalid;
            if (field.name === 'password' && field.value && [...field.value].length < 8) message = messages.password_min;
            if (field.name === 'password_confirmation') {
                const password = find('password')?.value;
                if (password && !field.value) message = messages.password_confirmation_required;
                else if (field.value && field.value !== password) message = messages.password_confirmed;
            }
            if (value && field.name === 'date_of_birth') {
                if (field.validity.badInput || (field.min && value < field.min)) message = messages.birth_date_invalid;
                else if (field.max && value > field.max) message = messages.birth_date_min_age;
            }
            const container = field.closest('label') || (field.name.startsWith('password') ? field.parentElement.parentElement : field.parentElement);
            container.querySelectorAll('[data-server-error]').forEach(error => { error.hidden = true; });
            if (!errors.has(field)) {
                const error = document.createElement('p');
                error.id = 'staff-live-error-' + field.name;
                error.className = 'mt-1 block text-xs font-medium text-red-600';
                error.setAttribute('aria-live', 'polite');
                container.append(error);
                errors.set(field, error);
                field.setAttribute('aria-describedby', error.id);
            }
            errors.get(field).textContent = message;
            field.setAttribute('aria-invalid', message ? 'true' : 'false');
            return !message;
        }

        for (const field of fields.filter(field => field.type !== 'file')) {
            for (const event of ['input', 'change', 'blur']) {
                field.addEventListener(event, () => {
                    touched.add(field);
                    validate(field);
                    if (field.name === 'password' && touched.has(find('password_confirmation'))) validate(find('password_confirmation'));
                });
            }
        }
        form.addEventListener('submit', event => {
            const invalid = fields.filter(field => field.type !== 'file').filter(field => !validate(field));
            if (invalid.length) {
                event.preventDefault();
                invalid[0].focus();
            }
        });
    });
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initializeStaffForms);
else initializeStaffForms();
