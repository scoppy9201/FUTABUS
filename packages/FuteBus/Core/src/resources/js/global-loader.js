const loader = document.getElementById('global-page-loader');

if (loader) {
    const animationContainer = loader.querySelector('.global-page-loader__animation');
    let animation;
    let showTimer;

    function mountAnimation() {
        if (animation || !window.lottie || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

        animation = window.lottie.loadAnimation({
            container: animationContainer,
            renderer: 'svg',
            loop: true,
            autoplay: false,
            path: loader.dataset.animationUrl,
        });
        animation.addEventListener('DOMLoaded', () => {
            loader.classList.add('has-animation');
            if (loader.classList.contains('is-visible')) animation.play();
        });
    }

    function show(delay = 120) {
        window.clearTimeout(showTimer);
        const reveal = () => {
            mountAnimation();
            loader.classList.add('is-visible');
            loader.setAttribute('aria-hidden', 'false');
            animation?.play();
        };
        if (delay === 0) reveal();
        else showTimer = window.setTimeout(reveal, delay);
    }

    function hide() {
        window.clearTimeout(showTimer);
        loader.classList.remove('is-visible');
        loader.setAttribute('aria-hidden', 'true');
        animation?.pause();
    }

    window.FutaLoader = { show, hide };
    if (document.readyState !== 'complete') show(250);
    document.addEventListener('DOMContentLoaded', mountAnimation, { once: true });
    window.addEventListener('load', () => { mountAnimation(); hide(); }, { once: true });
    window.addEventListener('pageshow', hide);

    document.addEventListener('click', (event) => {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        const link = event.target instanceof Element ? event.target.closest('a[href]') : null;
        if (!link || link.hasAttribute('download') || link.dataset.noLoader !== undefined || (link.target && link.target !== '_self')) return;

        if (link.getAttribute('href')?.startsWith('#')) return;
        const destination = new URL(link.href, window.location.href);
        if (destination.origin !== window.location.origin || !['http:', 'https:'].includes(destination.protocol)) return;
        if (destination.pathname === window.location.pathname && destination.search === window.location.search && destination.hash) return;
        show();
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (event.defaultPrevented || !(form instanceof HTMLFormElement) || form.dataset.noLoader !== undefined) return;
        if (form.target && form.target !== '_self') return;
        show();
    });
}
