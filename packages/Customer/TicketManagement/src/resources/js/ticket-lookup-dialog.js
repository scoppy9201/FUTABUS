const lookupDialog = document.getElementById('ticket-lookup-not-found');

if (lookupDialog instanceof HTMLDialogElement) {
    const okButton = lookupDialog.querySelector('[data-lookup-dialog-ok]');
    const previousBodyOverflow = document.body.style.overflow;
    const previousRootOverflow = document.documentElement.style.overflow;

    lookupDialog.addEventListener('close', () => {
        document.body.style.overflow = previousBodyOverflow;
        document.documentElement.style.overflow = previousRootOverflow;
    });

    okButton?.addEventListener('click', () => lookupDialog.close());

    lookupDialog.showModal();
    document.body.style.overflow = 'hidden';
    document.documentElement.style.overflow = 'hidden';
    okButton?.focus();
}
