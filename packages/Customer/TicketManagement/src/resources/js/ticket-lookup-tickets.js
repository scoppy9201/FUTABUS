const carousel = document.querySelector('[data-lookup-tickets]');

if (carousel) {
    const tickets = [...carousel.querySelectorAll('[data-ticket-slide]')];
    const previous = carousel.querySelector('[data-ticket-prev]');
    const next = carousel.querySelector('[data-ticket-next]');
    const current = carousel.querySelector('[data-ticket-current]');
    let index = 0;

    const show = (position) => {
        index = Math.max(0, Math.min(position, tickets.length - 1));
        tickets.forEach((ticket, ticketIndex) => {
            ticket.hidden = ticketIndex !== index;
            ticket.style.display = ticketIndex === index ? 'flex' : 'none';
        });
        if (previous) previous.disabled = index === 0;
        if (next) next.disabled = index === tickets.length - 1;
        if (current) current.textContent = String(index + 1);
    };

    previous?.addEventListener('click', () => show(index - 1));
    next?.addEventListener('click', () => show(index + 1));
    carousel.querySelector('[data-print-ticket]')?.addEventListener('click', () => window.print());
    carousel.querySelectorAll('[data-share-current]').forEach((button) => {
        button.addEventListener('click', async () => {
            const shareText = tickets[index]?.dataset.ticketShareText;
            if (!shareText) return;
            if (navigator.share) {
                try {
                    await navigator.share({ text: shareText });
                    return;
                } catch (error) {
                    if (error.name === 'AbortError') return;
                }
            }
            try {
                await navigator.clipboard.writeText(shareText);
                window.FutaNotify?.show(carousel.dataset.linkCopied, { tone: 'success' });
            } catch {
                window.prompt(shareText, shareText);
            }
        });
    });
    if (tickets.length) show(0);
}
