const checkout = document.querySelector('[data-payment-checkout]');
const exitForm = checkout?.querySelector('[data-payment-exit-form]');

if (exitForm) {
    let leaving = false;
    let confirming = false;
    const paymentUrl = window.location.href;

    exitForm.addEventListener('submit', async (event) => {
        if (leaving) return;
        event.preventDefault();
        if (confirming) return;
        confirming = true;
        try {
            const accepted = await window.FutaConfirm?.({
                title: exitForm.dataset.confirmTitle,
                message: exitForm.dataset.confirmMessage,
            });
            if (!accepted) return;
            exitForm.elements.namedItem('confirm_exit').value = '1';
            leaving = true;
            exitForm.requestSubmit();
        } finally {
            confirming = false;
        }
    });

    // Browser Back uses the same confirmation; a reload keeps the existing hold.
    window.history.pushState({ futaPaymentGuard: true }, '', paymentUrl);
    window.addEventListener('popstate', () => {
        if (leaving) return;
        window.history.pushState({ futaPaymentGuard: true }, '', paymentUrl);
        exitForm.requestSubmit();
    });
    window.addEventListener('beforeunload', (event) => {
        if (leaving) return;
        event.preventDefault();
        event.returnValue = '';
    });
    window.addEventListener('futa:payment-terminal', () => { leaving = true; });
    const enableExit = () => {
        if (typeof window.FutaConfirm === 'function') {
            exitForm.querySelector('[data-payment-exit]').disabled = false;
        }
    };
    enableExit();
    window.addEventListener('load', enableExit, { once: true });
}
