const createOtpFlow = (expiresAt = 0, resendAt = 0) => ({
    otp: Array(6).fill(''),
    secondsRemaining: 0,
    countdownTimer: null,
    resendRemaining: Math.max(0, resendAt - Math.floor(Date.now() / 1000)),

    init() {
        if (expiresAt > 0) {
            const tick = () => {
                const now = Math.floor(Date.now() / 1000);
                this.secondsRemaining = Math.max(0, expiresAt - now);
                this.resendRemaining = Math.max(0, resendAt - now);
            };
            tick();
            this.countdownTimer = window.setInterval(tick, 1000);
            this.$nextTick(() => this.otpInputs()[0]?.focus());
        }
    },

    handleOtpInput(index, event) {
        const digit = event.target.value.replace(/\D/g, '').slice(-1);

        this.otp[index] = digit;
        event.target.value = digit;

        if (digit && index < this.otp.length - 1) {
            this.otpInputs()[index + 1]?.focus();
        }
    },

    handleOtpKeydown(index, event) {
        if (event.key === 'Backspace' && ! this.otp[index] && index > 0) {
            this.otpInputs()[index - 1]?.focus();
        }

        if (event.key === 'ArrowLeft' && index > 0) {
            event.preventDefault();
            this.otpInputs()[index - 1]?.focus();
        }

        if (event.key === 'ArrowRight' && index < this.otp.length - 1) {
            event.preventDefault();
            this.otpInputs()[index + 1]?.focus();
        }
    },

    handleOtpPaste(event) {
        const digits = event.clipboardData
            .getData('text')
            .replace(/\D/g, '')
            .slice(0, this.otp.length)
            .split('');

        if (digits.length === 0) {
            return;
        }

        event.preventDefault();
        this.otp = this.otp.map((_, index) => digits[index] ?? '');
        this.$nextTick(() => this.otpInputs()[Math.min(digits.length, this.otp.length) - 1]?.focus());
    },

    submitOtp() {
        const firstEmptyIndex = this.otp.findIndex((digit) => digit === '');

        if (firstEmptyIndex !== -1) {
            this.otpInputs()[firstEmptyIndex]?.focus();
            return false;
        }

        return true;
    },

    otpInputs() {
        return [...this.$root.querySelectorAll('[data-otp-input]')];
    },

    formattedCountdown() {
        const minutes = Math.floor(this.secondsRemaining / 60);
        const seconds = String(this.secondsRemaining % 60).padStart(2, '0');

        return `${String(minutes).padStart(2, '0')}:${seconds}`;
    },

    destroy() {
        window.clearInterval(this.countdownTimer);
    },
});

document.addEventListener('alpine:init', () => {
    Alpine.data('authOtp', createOtpFlow);
});
