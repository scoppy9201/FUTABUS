const successModal = document.querySelector('[data-payment-success-modal]');

if (successModal) {
    successModal.showModal();
    const animationContainer = document.querySelector('[data-payment-success-animation]');
    let animation;
    const startAnimation = () => {
        if (!animationContainer || !window.lottie || animation) return;
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        animation = window.lottie.loadAnimation({
            container: animationContainer,
            renderer: 'svg',
            loop: false,
            autoplay: !reducedMotion,
            path: animationContainer.dataset.animationUrl,
        });
        if (reducedMotion) {
            animation.addEventListener('DOMLoaded', () => {
                animation.goToAndStop(animation.totalFrames - 1, true);
            });
        }
    };
    startAnimation();
    window.addEventListener('load', startAnimation, { once: true });
    successModal.addEventListener('cancel', (event) => {
        event.preventDefault();
        window.location.assign(successModal.dataset.homeUrl);
    });
    document.querySelector('[data-open-payment-success]')?.addEventListener('click', () => {
        if (!successModal.open) successModal.showModal();
    });
}

const ticketList = document.querySelector('[data-ticket-list]');

if (ticketList) {
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
