document.querySelectorAll('[data-ticket-history-actions]').forEach((trigger) => {
    const menu = document.getElementById(trigger.getAttribute('aria-controls'));
    if (!menu || typeof menu.showPopover !== 'function') return;

    const positionMenu = () => {
        const triggerRect = trigger.getBoundingClientRect();
        const menuRect = menu.getBoundingClientRect();
        const top = triggerRect.bottom + menuRect.height + 8 > window.innerHeight
            ? triggerRect.top - menuRect.height - 8
            : triggerRect.bottom + 8;

        menu.style.top = `${Math.max(8, top)}px`;
        menu.style.left = `${Math.max(8, Math.min(triggerRect.right - menuRect.width, window.innerWidth - menuRect.width - 8))}px`;
    };

    trigger.addEventListener('click', () => {
        if (menu.matches(':popover-open')) {
            menu.hidePopover();
            return;
        }

        menu.querySelectorAll('[data-history-sensitive]').forEach((action) => {
            const expired = Date.now() + 24 * 60 * 60 * 1000 >= Number(action.dataset.departure) * 1000;
            action.setAttribute('aria-disabled', String(expired));
            action.classList.toggle('pointer-events-none', expired);
            action.classList.toggle('text-slate-400', expired);
            action.classList.toggle('text-gray-900', !expired);
        });

        menu.showPopover();
        positionMenu();
    });

    menu.addEventListener('toggle', (event) => {
        trigger.setAttribute('aria-expanded', String(event.newState === 'open'));
    });

    menu.addEventListener('click', (event) => {
        const action = event.target.closest('[data-history-sensitive]');
        if (action?.getAttribute('aria-disabled') === 'true') event.preventDefault();
    });

    window.addEventListener('resize', () => {
        if (menu.matches(':popover-open')) positionMenu();
    });
    document.addEventListener('scroll', () => {
        if (menu.matches(':popover-open')) positionMenu();
    }, true);
});
