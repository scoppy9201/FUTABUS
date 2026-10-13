window.staffPassword = (endpoints, messages) => ({
    ...window.staffProfile(endpoints, messages),
    passwords: { current_password: '', password: '', password_confirmation: '' },
    validate(field) {
        const value = this.passwords[field];
        const rules = messages.validation;
        let error = null;
        if (field === 'current_password' && !value) error = rules.current_required;
        if (field === 'password') error = !value ? rules.new_required : value.length < 8 ? rules.new_min : null;
        if (field === 'password_confirmation') error = !value ? rules.confirm_required : value !== this.passwords.password ? rules.confirmed : null;
        if (error) this.errors[field] = [error];
        else delete this.errors[field];
        if (field === 'password' && this.passwords.password_confirmation) this.validate('password_confirmation');
    },
    async save() {
        if (this.saving || !this.employee) return;
        Object.keys(this.passwords).forEach(field => this.validate(field));
        if (Object.keys(this.errors).length) return;
        this.saving = true;
        this.errors = {};
        this.message = '';
        try {
            const fields = Object.fromEntries(new FormData(this.$refs.form));
            const { data } = await window.axios.put(endpoints.update, fields, { params: { lang: document.documentElement.lang } });
            this.$refs.form.reset();
            this.passwords = { current_password: '', password: '', password_confirmation: '' };
            this.message = data.message;
            window.FutaNotify[data.notification_sent ? 'success' : 'warning'](data.message, { duration: 4000 });
        } catch (error) {
            this.errors = error.response?.data?.errors || {};
            if (error.response?.status !== 422) {
                this.message = messages.error;
                window.FutaNotify.error(this.message, { duration: 4000 });
            }
        } finally {
            this.saving = false;
        }
    },
});
