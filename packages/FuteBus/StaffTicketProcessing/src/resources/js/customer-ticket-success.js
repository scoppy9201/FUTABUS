export function initTicketSuccess(root, errorMessage) {
const ticketList = root.querySelector('[data-ticket-list]');

if (ticketList) {
    root.querySelectorAll('[data-ticket-scroll]').forEach((button) => {
        button.addEventListener('click', () => {
            const card = ticketList.querySelector('.ticket-success__ticket');
            const distance = (card?.getBoundingClientRect().width || ticketList.clientWidth) + (parseFloat(getComputedStyle(ticketList).columnGap) || 0);
            ticketList.scrollBy({ left: button.dataset.ticketScroll === 'next' ? distance : -distance, behavior: 'smooth' });
        });
    });

    root.querySelectorAll('[data-share-ticket]').forEach((button) => {
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
                window.FutaNotify?.error(errorMessage);
            }
        });
    });

    root.querySelectorAll('[data-print-tickets]').forEach(button => button.addEventListener('click', async () => {
        if (button.disabled) return;
        button.disabled = true;
        const frame = document.createElement('iframe');
        frame.className = 'fixed -left-[10000px] top-0 h-[1000px] w-[800px] border-0';
        frame.setAttribute('aria-hidden', 'true');
        frame.tabIndex = -1;
        document.body.append(frame);
        try {
            const printDocument = frame.contentDocument;
            printDocument.documentElement.lang = document.documentElement.lang;
            printDocument.title = root.querySelector('#ticket-success-title')?.textContent || document.title;
            printDocument.body.className = 'bg-white text-gray-900';
            const styles = [...document.querySelectorAll('link[rel="stylesheet"]')].map(source => new Promise((resolve, reject) => {
                const link = source.cloneNode();
                link.onload = resolve;
                link.onerror = reject;
                printDocument.head.append(link);
            }));
            document.querySelectorAll('style').forEach(style => printDocument.head.append(style.cloneNode(true)));
            ticketList.querySelectorAll('.ticket-success__ticket').forEach(ticket => printDocument.body.append(ticket.cloneNode(true)));
            await Promise.all(styles);
            await Promise.all([...printDocument.images].map(image => image.decode()));
            await printDocument.fonts.ready;
            frame.contentWindow.addEventListener('afterprint', () => frame.remove(), { once: true });
            frame.contentWindow.focus();
            frame.contentWindow.print();
        } catch {
            frame.remove();
            window.FutaNotify?.error(errorMessage);
        } finally {
            button.disabled = false;
        }
    }));
}

}
