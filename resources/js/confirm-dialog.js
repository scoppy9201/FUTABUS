const dialog = document.getElementById('global-confirm-dialog');

if (dialog) {
    const title = dialog.querySelector('#global-confirm-title');
    const message = dialog.querySelector('#global-confirm-message');
    const cancel = dialog.querySelector('[data-confirm-cancel]');
    const accept = dialog.querySelector('[data-confirm-accept]');
    const panel = dialog.querySelector('[data-confirm-panel]');
    const allowedForms = new WeakSet();

    let pendingForm = null;
    let pendingSubmitter = null;
    let previousFocus = null;
    let isClosing = false;

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    function animateBackdrop(keyframes, duration) {
        try {
            return dialog.animate(keyframes, {
                duration,
                easing: 'ease-out',
                pseudoElement: '::backdrop',
            });
        } catch {
            return null;
        }
    }

    async function closeDialog() {
        if (isClosing || !dialog.open) {
            return;
        }

        isClosing = true;
        let panelAnimation = null;
        let backdropAnimation = null;

        if (!reducedMotion.matches) {
            panelAnimation = panel.animate(
                [
                    { opacity: 1, transform: 'translateY(0) scale(1)' },
                    { opacity: 0, transform: 'translateY(8px) scale(0.96)' },
                ],
                { duration: 150, easing: 'ease-in', fill: 'forwards' },
            );
            backdropAnimation = animateBackdrop(
                [{ opacity: 1 }, { opacity: 0 }],
                150,
            );

            await Promise.all([
                panelAnimation.finished,
                backdropAnimation?.finished,
            ].filter(Boolean).map((finished) => finished.catch(() => {})));
        }

        dialog.close();
        panelAnimation?.cancel();
        backdropAnimation?.cancel();
        isClosing = false;
    }

    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-confirm')) {
            return;
        }

        if (allowedForms.delete(form)) {
            return;
        }

        event.preventDefault();
        pendingForm = form;
        pendingSubmitter = event.submitter;
        previousFocus = document.activeElement;

        title.textContent = form.dataset.confirmTitle || dialog.dataset.defaultTitle;
        message.textContent = form.dataset.confirmMessage || dialog.dataset.defaultMessage;
        accept.textContent = form.dataset.confirmLabel || dialog.dataset.defaultConfirm;
        accept.disabled = false;

        dialog.showModal();
        cancel.focus();

        if (!reducedMotion.matches) {
            panel.animate(
                [
                    { opacity: 0, transform: 'translateY(18px) scale(0.88)' },
                    { opacity: 1, transform: 'translateY(-3px) scale(1.025)', offset: 0.68 },
                    { opacity: 1, transform: 'translateY(0) scale(1)' },
                ],
                { duration: 420, easing: 'cubic-bezier(0.22, 1, 0.36, 1)' },
            );
            animateBackdrop([{ opacity: 0 }, { opacity: 1 }], 240);
        }
    });

    cancel.addEventListener('click', () => void closeDialog());
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            void closeDialog();
        }
    });
    dialog.addEventListener('cancel', (event) => {
        event.preventDefault();
        void closeDialog();
    });

    dialog.addEventListener('close', () => {
        pendingForm = null;
        pendingSubmitter = null;
        accept.disabled = false;
        previousFocus?.focus();
        previousFocus = null;
    });

    accept.addEventListener('click', async () => {
        const form = pendingForm;
        const submitter = pendingSubmitter;

        if (!form) {
            return;
        }

        accept.disabled = true;
        await closeDialog();
        allowedForms.add(form);
        form.requestSubmit(submitter || undefined);
    });
}
