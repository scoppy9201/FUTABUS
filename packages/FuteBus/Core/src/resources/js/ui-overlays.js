export function createModalState() {
    let stack = [];
    return {
        sync(visible) {
            const present = new Set(visible);
            stack = stack.filter((modal) => present.has(modal));
            visible.forEach((modal) => {
                if (!stack.includes(modal)) stack.push(modal);
            });
            return stack.at(-1) ?? null;
        },
    };
}

export function shouldLockScroller(scroller, activeModal) {
    return activeModal !== null && !activeModal.contains(scroller);
}

export function createScrollbarActivity(schedule = setTimeout, cancel = clearTimeout) {
    const timers = new Map();
    const hide = (element) => {
        cancel(timers.get(element));
        timers.delete(element);
        element.classList.remove('ui-scrolling', 'ui-scrollbar-hover');
    };
    return {
        scroll(element) {
            cancel(timers.get(element));
            element.classList.add('ui-scrolling');
            timers.set(element, schedule(() => hide(element), 800));
        },
        hide,
        hideAll() {
            [...timers.keys()].forEach(hide);
        },
    };
}

export function pointerOnScrollbar(pointer, box, dimensions) {
    const { vertical, horizontal, gutter = 8, rtl = false } = dimensions;
    const inside = pointer.x >= box.left && pointer.x <= box.right
        && pointer.y >= box.top && pointer.y <= box.bottom;
    if (!inside) return false;
    const verticalEdge = rtl ? pointer.x <= box.left + gutter : pointer.x >= box.right - gutter;
    return (vertical && verticalEdge) || (horizontal && pointer.y >= box.bottom - gutter);
}

export function initUiOverlays(doc = document, browser = window) {
    if (doc.documentElement.dataset.uiOverlaysReady) return;
    doc.documentElement.dataset.uiOverlaysReady = 'true';

    const modalState = createModalState();
    const activity = createScrollbarActivity();
    const scrollers = new Set();
    const locked = new Set();
    let activeModal = null;
    let scheduled = false;

    const visible = (element) => {
        if (!element.isConnected || element.closest('[hidden], [x-cloak]')) return false;
        const style = browser.getComputedStyle(element);
        return style.display !== 'none' && style.visibility !== 'hidden' && element.getClientRects().length > 0;
    };

    const modalVisible = (element) => {
        if (element.tagName === 'DIALOG') {
            try {
                return element.matches(':modal');
            } catch {
                return element.open && element.getAttribute('aria-modal') === 'true';
            }
        }
        return visible(element);
    };

    const refresh = () => {
        scheduled = false;
        activeModal = modalState.sync([...doc.querySelectorAll('dialog[open], [aria-modal="true"]')].filter(modalVisible));
        doc.documentElement.classList.toggle('ui-modal-open', activeModal !== null);

        doc.querySelectorAll('*').forEach((element) => {
            if (element === doc.documentElement || element === doc.body) return;
            const style = browser.getComputedStyle(element);
            const scrollable = /(auto|scroll|overlay)/.test(style.overflowX + style.overflowY);
            if (scrollable && (element.scrollHeight > element.clientHeight || element.scrollWidth > element.clientWidth)) {
                element.classList.add('ui-scrollable');
                scrollers.add(element);
            }
        });
        scrollers.add(doc.scrollingElement ?? doc.documentElement);

        const backgrounds = new Set([doc.documentElement, doc.body, ...scrollers]);
        backgrounds.forEach((element) => {
            if (!element.isConnected) {
                scrollers.delete(element);
                locked.delete(element);
                activity.hide(element);
                return;
            }
            const lock = shouldLockScroller(element, activeModal);
            element.classList.toggle('ui-scroll-locked', lock);
            if (lock) locked.add(element);
            else locked.delete(element);
        });
    };

    const requestRefresh = () => {
        if (scheduled) return;
        scheduled = true;
        browser.requestAnimationFrame(refresh);
    };

    doc.addEventListener('scroll', (event) => {
        const element = event.target === doc ? doc.scrollingElement : event.target;
        if (!(element instanceof browser.Element) || locked.has(element)) return;
        element.classList.add('ui-scrollable');
        scrollers.add(element);
        activity.scroll(element);
    }, { capture: true, passive: true });

    doc.addEventListener('pointermove', (event) => {
        if (event.pointerType === 'touch') return;
        scrollers.forEach((element) => {
            if (!element.isConnected || locked.has(element)) return;
            const root = element === doc.scrollingElement;
            const box = root ? { left: 0, top: 0, right: browser.innerWidth, bottom: browser.innerHeight }
                : element.getBoundingClientRect();
            const dimensions = {
                vertical: element.scrollHeight > element.clientHeight,
                horizontal: element.scrollWidth > element.clientWidth,
                rtl: browser.getComputedStyle(element).direction === 'rtl',
                gutter: Math.max(8, element.offsetWidth - element.clientWidth),
            };
            element.classList.toggle('ui-scrollbar-hover', pointerOnScrollbar(
                { x: event.clientX, y: event.clientY }, box, dimensions,
            ));
        });
    }, { passive: true });

    doc.addEventListener('pointerout', (event) => {
        scrollers.forEach((element) => {
            if (event.relatedTarget && element.contains(event.relatedTarget)) return;
            if (event.target === element || element.contains(event.target)) activity.hide(element);
        });
    }, { passive: true });

    const blockBackgroundScroll = (event) => {
        if (activeModal && !activeModal.contains(event.target) && event.cancelable) event.preventDefault();
    };
    doc.addEventListener('wheel', blockBackgroundScroll, { capture: true, passive: false });
    doc.addEventListener('touchmove', blockBackgroundScroll, { capture: true, passive: false });
    doc.addEventListener('close', requestRefresh, true);
    doc.addEventListener('cancel', requestRefresh, true);
    browser.addEventListener('resize', requestRefresh, { passive: true });
    browser.addEventListener('blur', () => {
        activity.hideAll();
        scrollers.forEach((element) => element.classList.remove('ui-scrollbar-hover'));
    });

    const withoutOwnClasses = (value) => (value ?? '').replace(/\bui-(scrollable|scrolling|scrollbar-hover|scroll-locked|modal-open)\b/g, '')
        .replace(/\s+/g, ' ').trim();
    const observer = new browser.MutationObserver((changes) => {
        const meaningful = changes.some((change) => change.attributeName !== 'class'
            || withoutOwnClasses(change.oldValue) !== withoutOwnClasses(change.target.getAttribute('class')));
        if (meaningful) requestRefresh();
    });
    observer.observe(doc.documentElement, {
        childList: true, subtree: true, attributes: true, attributeOldValue: true,
        attributeFilter: ['open', 'aria-modal', 'class', 'style', 'hidden', 'x-cloak'],
    });
    refresh();
}

if (typeof document !== 'undefined' && typeof window !== 'undefined') {
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => initUiOverlays(), { once: true });
    else initUiOverlays();
}
