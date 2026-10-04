const dialog = document.getElementById('global-confirm-dialog');

if (dialog) {
    const title = dialog.querySelector('#global-confirm-title');
    const message = dialog.querySelector('#global-confirm-message');
    const cancel = dialog.querySelector('[data-confirm-cancel]');
    const accept = dialog.querySelector('[data-confirm-accept]');
    const warningIcon = dialog.querySelector('[data-dialog-warning-icon]');
    const infoIcon = dialog.querySelector('[data-dialog-info-icon]');
    const allowedForms = new WeakSet();

    let pendingForm = null;
    let pendingSubmitter = null;
    let previousFocus = null;
    let isClosing = false;
    let openAnimation = null;
    let openBackdropAnimation = null;
    let noticeMode = false;

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
        openAnimation?.cancel();
        openBackdropAnimation?.cancel();
        let dialogAnimation = null;
        let backdropAnimation = null;

        if (!reducedMotion.matches) {
            dialogAnimation = dialog.animate(
                [
                    { opacity: 1, transform: 'translateY(0) scale(1)' },
                    { opacity: 0, transform: 'translateY(-8px) scale(0.97, 0.92)' },
                ],
                { duration: 170, easing: 'ease-in', fill: 'forwards' },
            );
            backdropAnimation = animateBackdrop(
                [{ opacity: 1 }, { opacity: 0 }],
                170,
            );

            await Promise.all([
                dialogAnimation.finished,
                backdropAnimation?.finished,
            ].filter(Boolean).map((finished) => finished.catch(() => {})));
        }

        dialog.close();
        dialogAnimation?.cancel();
        backdropAnimation?.cancel();
        isClosing = false;
    }

    function openDialog(source, isNotice = false) {
        if (dialog.open || isClosing) {
            return;
        }

        noticeMode = isNotice;
        previousFocus = document.activeElement;
        title.textContent = source.dataset.noticeTitle || source.dataset.confirmTitle || dialog.dataset.defaultTitle;
        message.textContent = source.dataset.noticeMessage || source.dataset.confirmMessage || dialog.dataset.defaultMessage;
        accept.textContent = source.dataset.noticeLabel || source.dataset.confirmLabel || dialog.dataset.defaultConfirm;
        cancel.hidden = isNotice;
        accept.disabled = false;
        const showWarning = !isNotice || source.dataset.noticeTone === 'warning';
        warningIcon.classList.toggle('grid', showWarning);
        warningIcon.classList.toggle('hidden', !showWarning);
        infoIcon.classList.toggle('grid', !showWarning);
        infoIcon.classList.toggle('hidden', showWarning);

        dialog.showModal();
        (isNotice ? accept : cancel).focus();

        if (!reducedMotion.matches) {
            openAnimation = dialog.animate(
                [
                    { opacity: 0, transform: 'translateY(-22px) scale(0.82, 0.68)' },
                    { opacity: 1, transform: 'translateY(2px) scale(1.018, 1.012)', offset: 0.64 },
                    { opacity: 1, transform: 'translateY(-1px) scale(0.996)', offset: 0.84 },
                    { opacity: 1, transform: 'translateY(0) scale(1)' },
                ],
                { duration: 460, easing: 'cubic-bezier(0.16, 1, 0.3, 1)' },
            );
            openBackdropAnimation = animateBackdrop([{ opacity: 0 }, { opacity: 1 }], 260);
        }
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
        openDialog(form);
    });

    document.addEventListener('click', (event) => {
        const trigger = event.target instanceof Element
            ? event.target.closest('[data-notice-trigger]')
            : null;

        if (trigger) {
            event.preventDefault();
            openDialog(trigger, true);
        }
    });

    const initialNotice = document.querySelector('[data-notice-on-load]');
    if (initialNotice) {
        requestAnimationFrame(() => openDialog(initialNotice, true));
    }

    cancel.addEventListener('click', () => void closeDialog());
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog && !noticeMode) {
            void closeDialog();
        }
    });
    dialog.addEventListener('cancel', (event) => {
        event.preventDefault();
        if (!noticeMode) void closeDialog();
    });

    dialog.addEventListener('close', () => {
        pendingForm = null;
        pendingSubmitter = null;
        accept.disabled = false;
        previousFocus?.focus();
        previousFocus = null;
        noticeMode = false;
    });

    accept.addEventListener('click', async () => {
        if (noticeMode) {
            accept.disabled = true;
            await closeDialog();
            return;
        }

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
