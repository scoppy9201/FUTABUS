import { composeReviewComment, ratingGroup, toggleRating } from './ticket-review-state.js';

const dialog = document.querySelector('[data-history-dialog]');
const content = dialog?.querySelector('[data-history-dialog-content]');
const historyPage = document.querySelector('.ticket-history-page');

if (historyPage && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
    let nearPageScrollbar = false;
    document.addEventListener('pointermove', (event) => {
        const nearEdge = event.clientX >= window.innerWidth - 24;
        if (nearEdge === nearPageScrollbar) return;
        nearPageScrollbar = nearEdge;
        document.documentElement.classList.toggle('ticket-history-page-scroll-hover', nearEdge);
    }, { passive: true });
    window.addEventListener('blur', () => {
        nearPageScrollbar = false;
        document.documentElement.classList.remove('ticket-history-page-scroll-hover');
    });
}

function syncReviewForm(form) {
    const rating = Number(form.elements.rating.value);
    const group = ratingGroup(rating);
    form.querySelectorAll('[data-review-star]').forEach((button) => {
        const selected = Number(button.dataset.reviewStar) <= rating;
        button.setAttribute('aria-pressed', String(Number(button.dataset.reviewStar) === rating));
        button.querySelector('svg').classList.toggle('text-amber-500', selected);
        button.querySelector('svg').classList.toggle('text-slate-300', !selected);
    });
    const ratingLabels = form.querySelector('[data-review-rating-labels]');
    ratingLabels.classList.toggle('hidden', rating === 0);
    ratingLabels.querySelectorAll('[data-review-rating-label]').forEach((label) => {
        label.hidden = Number(label.dataset.reviewRatingLabel) !== rating;
    });
    form.querySelectorAll('[data-review-tags]').forEach((tags) => {
        const visible = tags.dataset.reviewTags === group;
        tags.classList.toggle('hidden', !visible);
        tags.classList.toggle('flex', visible);
        if (!visible) tags.querySelectorAll('[data-review-suggestion]').forEach((button) => {
            button.setAttribute('aria-pressed', 'false');
        });
    });
    const tags = [...form.querySelectorAll(`[data-review-tags="${group}"] [data-review-suggestion][aria-pressed="true"]`)]
        .map((button) => button.dataset.reviewSuggestion);
    const comment = form.querySelector('[data-review-comment]').value;
    const combined = composeReviewComment(tags, comment);
    form.elements.comment.value = combined;
    form.querySelector('[data-review-count]').textContent = `${Array.from(comment).length}/300`;
    const tooLong = Array.from(combined).length > 300;
    form.querySelector('[data-review-error]').textContent = tooLong ? form.dataset.reviewTooLong : '';
    form.querySelector('[type="submit"]').disabled = rating === 0 || tooLong;
}

document.addEventListener('click', (event) => {
    const star = event.target.closest('[data-review-star]');
    if (!star) return;
    const form = star.closest('[data-history-review]');
    form.elements.rating.value = toggleRating(Number(form.elements.rating.value), Number(star.dataset.reviewStar));
    form.dataset.dirty = 'true';
    syncReviewForm(form);
});

document.addEventListener('input', (event) => {
    if (!event.target.matches('[data-history-review] [data-review-comment]')) return;
    const form = event.target.closest('[data-history-review]');
    form.dataset.dirty = 'true';
    syncReviewForm(form);
});

document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-review-suggestion]');
    if (!button) return;
    const form = button.closest('[data-history-review]');
    button.setAttribute('aria-pressed', String(button.getAttribute('aria-pressed') !== 'true'));
    form.dataset.dirty = 'true';
    syncReviewForm(form);
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
            const result = await response.json();
            if (result.stage === 'rated') {
                const status = activeTrigger?.closest('tr')?.querySelector('[data-history-stage]');
                if (status) {
                    status.textContent = result.stageLabel;
                    status.className = 'inline-flex rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700';
                }
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
