const lookupDialog = document.getElementById('ticket-lookup-not-found');

if (lookupDialog instanceof HTMLDialogElement) {
    const okButton = lookupDialog.querySelector('[data-lookup-dialog-ok]');
    okButton?.addEventListener('click', () => lookupDialog.close());

    lookupDialog.showModal();
    okButton?.focus();
}
