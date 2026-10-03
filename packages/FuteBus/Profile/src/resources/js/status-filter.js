function initializeStatusFilter(picker) {
    const toggle = picker.querySelector('[data-ticket-status-toggle]');
    const label = picker.querySelector('[data-ticket-status-label]');
    const input = picker.querySelector('[data-ticket-status-value]');
    const list = picker.querySelector('[data-ticket-status-options]');
    const options = [...picker.querySelectorAll('[data-ticket-status-option]')];

    function close(restoreFocus = false) {
        list.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
        if (restoreFocus) toggle.focus();
    }

    function open(focusIndex = -1) {
        list.hidden = false;
        toggle.setAttribute('aria-expanded', 'true');
        const selectedIndex = options.findIndex((option) => option.dataset.ticketStatusOption === input.value);
        options[focusIndex >= 0 ? focusIndex : Math.max(selectedIndex, 0)].focus();
    }

    function select(option) {
        input.value = option.dataset.ticketStatusOption;
        label.textContent = option.textContent.trim();
        options.forEach((item) => item.setAttribute('aria-selected', String(item === option)));
        close(true);
    }

    toggle.addEventListener('click', () => {
        if (list.hidden) open();
        else close();
    });
    toggle.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            open(event.key === 'ArrowUp' ? options.length - 1 : 0);
        }
    });

    options.forEach((option) => option.addEventListener('click', () => select(option)));
    list.addEventListener('keydown', (event) => {
        const index = options.indexOf(document.activeElement);
        let nextIndex = index;

        if (event.key === 'ArrowDown') nextIndex = Math.min(index + 1, options.length - 1);
        else if (event.key === 'ArrowUp') nextIndex = Math.max(index - 1, 0);
        else if (event.key === 'Home') nextIndex = 0;
        else if (event.key === 'End') nextIndex = options.length - 1;
        else if (event.key === 'Escape') {
            event.preventDefault();
            close(true);
            return;
        } else if (event.key === 'Tab') {
            close();
            return;
        } else {
            return;
        }

        event.preventDefault();
        options[nextIndex].focus();
    });

    document.addEventListener('click', (event) => {
        if (!picker.contains(event.target)) close();
    });
}

document.querySelectorAll('[data-ticket-status-picker]').forEach(initializeStatusFilter);
