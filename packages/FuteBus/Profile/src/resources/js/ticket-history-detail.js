const dialog = document.querySelector('[data-history-dialog]');
const content = dialog?.querySelector('[data-history-dialog-content]');

document.addEventListener('change', (event) => {
    if (!event.target.matches('[data-history-review] [name="rating"]')) return;
    const form = event.target.closest('[data-history-review]');
    form.dataset.dirty = 'true';
    const selected = Number(event.target.value);
    form.querySelectorAll('[data-review-star]').forEach((label, index) => {
        const star = label.querySelector('svg');
        star.classList.toggle('text-futa-orange', index < selected);
        star.classList.toggle('text-slate-300', index >= selected);
    });
});

document.addEventListener('input', (event) => {
    if (!event.target.matches('[data-history-review] textarea')) return;
    const form = event.target.closest('[data-history-review]');
    form.dataset.dirty = 'true';
    form.querySelector('[data-review-count]').textContent = `${Array.from(event.target.value).length}/300`;
    form.querySelectorAll('[data-review-suggestion]').forEach((button) => {
        button.setAttribute('aria-pressed', String(event.target.value.includes(button.dataset.reviewSuggestion)));
    });
});

document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-review-suggestion]');
    if (!button) return;
    const textarea = button.closest('[data-history-review]').querySelector('textarea');
    const phrase = button.dataset.reviewSuggestion;
    if (textarea.value.includes(phrase)) return;
    const nextValue = [textarea.value.trim(), phrase].filter(Boolean).join(' · ');
    if (Array.from(nextValue).length > 300) return;
    textarea.value = nextValue;
    textarea.dispatchEvent(new Event('input', { bubbles: true }));
    textarea.focus();
});

if (dialog && content && typeof dialog.showModal === 'function') {
    let activeTrigger = null;
    let request = null;
    let reviewRequest = null;
    let refreshTimer = null;
    const showLoader = () => window.FutaLoader?.show(0);
    const hideLoader = () => window.FutaLoader?.hide();
    const loadDetail = async (url, signal) => {
        const target = new URL(url);
        target.searchParams.set('modal', '1');
        const response = await fetch(target, {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            signal,
            cache: 'no-store',
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        content.innerHTML = await response.text();
    };
    const refreshJourney = async () => {
        if (!dialog.open || !content.querySelector('article')
            || content.querySelector('[data-history-review][data-dirty]')) return;

        const scrollTop = dialog.scrollTop;
        request?.abort();
        request = new AbortController();
        try {
            await loadDetail(dialog.dataset.detailUrl, request.signal);
            dialog.scrollTop = scrollTop;
            content.querySelector('[data-history-close]')?.focus({ preventScroll: true });
        } catch {
            // Keep the last successful view until the next refresh.
        }
    };

    document.querySelectorAll('[data-history-detail]').forEach((link) => {
        link.addEventListener('click', async (event) => {
            event.preventDefault();
            const menu = link.closest('[popover]');
            activeTrigger = menu ? document.querySelector(`[aria-controls="${menu.id}"]`) : link;
            menu?.hidePopover();
            request?.abort();
            reviewRequest?.abort();
            const controller = new AbortController();
            request = controller;
            dialog.dataset.detailUrl = link.href;
            showLoader();
            let timedOut = false;
            const timeout = window.setTimeout(() => {
                timedOut = true;
                controller.abort();
            }, 15_000);

            try {
                await loadDetail(link.href, controller.signal);
                dialog.showModal();
                clearInterval(refreshTimer);
                refreshTimer = window.setInterval(refreshJourney, 60_000);
                content.querySelector('[data-history-close]')?.focus();
            } catch (error) {
                if (timedOut || error.name !== 'AbortError') window.location.assign(link.href);
            } finally {
                window.clearTimeout(timeout);
                if (request === controller) hideLoader();
            }
        });
    });

    dialog.addEventListener('submit', async (event) => {
        if (!event.target.matches('[data-history-review]')) return;
        event.preventDefault();
        const form = event.target;
        form.dataset.dirty = 'true';
        const submit = form.querySelector('[type="submit"]');
        const errorMessage = form.querySelector('[data-review-error]');
        const detailUrl = dialog.dataset.detailUrl;
        const controller = new AbortController();
        reviewRequest = controller;
        submit.disabled = true;
        errorMessage.textContent = '';
        showLoader();
        let timedOut = false;
        const timeout = window.setTimeout(() => {
            timedOut = true;
            controller.abort();
        }, 15_000);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
                body: new FormData(form),
                signal: controller.signal,
            });
            if (!dialog.open || dialog.dataset.detailUrl !== detailUrl) return;
            if (!response.ok) {
                const result = await response.json();
                errorMessage.textContent = result.errors?.rating?.[0]
                    || result.errors?.comment?.[0] || dialog.dataset.loadError;
                return;
            }
            await loadDetail(detailUrl, controller.signal);
            content.querySelector('[data-history-close]')?.focus();
        } catch (error) {
            if (timedOut || error.name !== 'AbortError') errorMessage.textContent = dialog.dataset.loadError;
        } finally {
            window.clearTimeout(timeout);
            submit.disabled = false;
            if (reviewRequest === controller) hideLoader();
        }
    });

    dialog.addEventListener('click', (event) => {
        if (event.target === dialog || event.target.closest('[data-history-close]')) dialog.close();
    });
    dialog.addEventListener('close', () => {
        clearInterval(refreshTimer);
        request?.abort();
        reviewRequest?.abort();
        activeTrigger?.focus();
    });
}
