const ticketList = document.querySelector('[data-ticket-list]');

if (ticketList) {
    document.querySelectorAll('[data-ticket-scroll]').forEach((button) => {
        button.addEventListener('click', () => {
            const card = ticketList.querySelector('.ticket-success__ticket');
            const distance = (card?.getBoundingClientRect().width || 340) + 18;
            ticketList.scrollBy({ left: button.dataset.ticketScroll === 'next' ? distance : -distance, behavior: 'smooth' });
        });
    });

    document.querySelectorAll('[data-share-ticket]').forEach((button) => {
        button.addEventListener('click', async () => {
            const url = button.dataset.shareUrl;
            const text = button.dataset.shareText;
            if (navigator.share) {
                try {
                    await navigator.share({ title: text, text, url });
                    return;
                } catch (error) {
                    if (error.name === 'AbortError') return;
                }
            }
            try {
                await navigator.clipboard.writeText(url);
                window.FutaNotify?.show(button.closest('.ticket-success')?.dataset.linkCopied || url, { tone: 'success' });
            } catch {
                window.prompt(text, url);
            }
        });
    });

    document.querySelector('[data-print-tickets]')?.addEventListener('click', () => window.print());
}
