let activePopup = null;

export function createPopup(panel, trigger, { onOpen, onClose, matchWidth = true } = {}) {
    let opened = false;
    let anchorFrame = null;
    const nativePopover = typeof panel.showPopover === 'function';
    if (nativePopover) {
        panel.setAttribute('popover', 'manual');
        panel.hidden = false;
    }
    else panel.hidden = true;
    trigger.setAttribute('aria-controls', panel.id);
    trigger.setAttribute('aria-expanded', 'false');
    panel.classList.add('futa-control-panel');

    function position() {
        if (!opened) return;
        const box = trigger.getBoundingClientRect();
        const padding = 12;
        if (box.width === 0 || box.height === 0) { close(); return; }
        if (matchWidth) panel.style.width = `${Math.min(box.width, window.innerWidth - padding * 2)}px`;
        panel.style.maxWidth = `${window.innerWidth - padding * 2}px`;
        const height = panel.scrollHeight;
        const below = window.innerHeight - box.bottom - padding;
        const above = box.top - padding;
        const upward = below < Math.min(height, 240) && above > below;
        panel.style.maxHeight = `${Math.max(0, Math.min(480, upward ? above - 8 : below - 8))}px`;
        const panelBox = panel.getBoundingClientRect();
        panel.style.left = `${Math.max(padding, Math.min(box.left, window.innerWidth - panelBox.width - padding))}px`;
        panel.style.top = `${upward ? Math.max(padding, box.top - panelBox.height - 8) : box.bottom + 8}px`;
    }

    function close(restoreFocus = false) {
        if (!opened) return;
        opened = false;
        if (nativePopover && panel.matches(':popover-open')) panel.hidePopover();
        else if (!nativePopover) panel.hidden = true;
        trigger.setAttribute('aria-expanded', 'false');
        if (activePopup === api) activePopup = null;
        cancelAnimationFrame(anchorFrame);
        onClose?.();
        if (restoreFocus) trigger.focus({ preventScroll: true });
    }

    function open() {
        if (trigger.disabled || opened) return;
        activePopup?.close();
        activePopup = api;
        opened = true;
        onOpen?.();
        if (!opened) return;
        if (nativePopover) panel.showPopover();
        else panel.hidden = false;
        trigger.setAttribute('aria-expanded', 'true');
        position();
    }

    const onOutsideClick = (event) => {
        if (opened && !panel.contains(event.target) && !trigger.contains(event.target)) close();
    };
    const onKeydown = (event) => {
        if (!opened) return;
        if (event.key === 'Escape') {
            event.preventDefault();
            event.stopPropagation();
            close(true);
        }
    };
    const onScroll = (event) => {
        if (!opened || panel.contains(event.target)) return;
        cancelAnimationFrame(anchorFrame);
        anchorFrame = requestAnimationFrame(position);
    };
    const onFocus = (event) => {
        if (opened && !panel.contains(event.target) && !trigger.contains(event.target)) close();
    };
    const onToggle = (event) => { if (event.newState === 'closed' && opened) close(); };
    document.addEventListener('click', onOutsideClick);
    document.addEventListener('focusin', onFocus);
    document.addEventListener('keydown', onKeydown, true);
    document.addEventListener('scroll', onScroll, true);
    window.addEventListener('resize', position);
    panel.addEventListener('toggle', onToggle);
    const api = {
        open, close, position,
        setTrigger(nextTrigger) {
            trigger.setAttribute('aria-expanded', 'false');
            trigger = nextTrigger;
            trigger.setAttribute('aria-controls', panel.id);
            trigger.setAttribute('aria-expanded', String(opened));
            position();
        },
        toggle() { if (opened) close(); else open(); },
        isOpen() { return opened; },
        destroy() {
            close();
            document.removeEventListener('click', onOutsideClick);
            document.removeEventListener('focusin', onFocus);
            document.removeEventListener('keydown', onKeydown, true);
            document.removeEventListener('scroll', onScroll, true);
            window.removeEventListener('resize', position);
            panel.removeEventListener('toggle', onToggle);
        },
    };
    return api;
}
