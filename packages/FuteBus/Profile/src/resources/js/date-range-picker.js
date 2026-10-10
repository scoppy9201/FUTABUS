function initializeDateRangePicker(picker) {
    const toggle = picker.querySelector('[data-range-toggle]');
    const calendar = picker.querySelector('[data-range-calendar]');
    const startLabel = picker.querySelector('[data-range-start-label]');
    const endLabel = picker.querySelector('[data-range-end-label]');
    const firstMonth = picker.querySelector('[data-range-first-month]');
    const secondMonth = picker.querySelector('[data-range-second-month]');
    const weekdayContainers = [...picker.querySelectorAll('[data-range-weekdays]')];
    const dayContainers = [...picker.querySelectorAll('[data-range-days]')];
    const locale = document.documentElement.lang === 'vi' ? 'vi-VN' : 'en-US';
    const shortDate = new Intl.DateTimeFormat(locale, { day: '2-digit', month: '2-digit', year: 'numeric' });
    const fullDate = new Intl.DateTimeFormat(locale, { dateStyle: 'full' });
    const monthTitle = new Intl.DateTimeFormat(locale, { month: 'short', year: 'numeric' });
    const weekday = new Intl.DateTimeFormat(locale, { weekday: 'short' });
    const placeholders = [startLabel.textContent.trim(), endLabel.textContent.trim()];
    const today = new Date();
    let visibleMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    let start = null;
    let end = null;

    weekdayContainers.forEach((container) => {
        for (let day = 0; day < 7; day++) {
            const label = document.createElement('span');
            label.textContent = weekday.format(new Date(2023, 0, day + 1));
            container.append(label);
        }
    });

    function sameDay(left, right) {
        return left && right && left.getTime() === right.getTime();
    }

    function close(restoreFocus = false) {
        calendar.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
        if (restoreFocus) toggle.focus();
    }

    function updateLabels() {
        [startLabel, endLabel].forEach((label, index) => {
            const value = index === 0 ? start : end;
            label.textContent = value ? shortDate.format(value) : placeholders[index];
            label.classList.toggle('text-slate-400', !value);
            label.classList.toggle('text-gray-950', Boolean(value));
        });
    }

    function choose(date) {
        if (!start || end) {
            start = date;
            end = null;
        } else if (date < start) {
            end = start;
            start = date;
        } else {
            end = date;
        }

        updateLabels();
        render();
        if (end) close(true);
    }

    function renderMonth(month, container) {
        container.replaceChildren();
        const leadingDays = month.getDay();
        const firstVisible = new Date(month.getFullYear(), month.getMonth(), 1 - leadingDays);

        for (let offset = 0; offset < 42; offset++) {
            const date = new Date(firstVisible.getFullYear(), firstVisible.getMonth(), firstVisible.getDate() + offset);
            const button = document.createElement('button');
            const endpoint = sameDay(date, start) || sameDay(date, end);
            const inRange = start && end && date > start && date < end;

            button.type = 'button';
            button.textContent = String(date.getDate());
            button.setAttribute('aria-label', fullDate.format(date));
            button.setAttribute('aria-pressed', String(Boolean(endpoint)));
            button.className = 'grid size-9 place-items-center rounded-lg text-sm focus-visible:z-10 focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-futa-orange';

            if (endpoint) {
                button.className += ' bg-futa-orange font-semibold text-white hover:bg-futa-orange-dark';
            } else if (inRange) {
                button.className += ' bg-futa-orange-soft font-medium text-futa-orange-dark hover:bg-futa-orange/20';
            } else if (date.getMonth() !== month.getMonth()) {
                button.className += ' text-gray-300 hover:bg-futa-orange-soft hover:text-futa-orange';
            } else if (sameDay(date, today)) {
                button.className += ' font-semibold text-futa-orange ring-1 ring-futa-orange hover:bg-futa-orange-soft';
            } else {
                button.className += ' text-gray-900 hover:bg-futa-orange-soft hover:text-futa-orange';
            }

            button.addEventListener('click', () => choose(date));
            container.append(button);
        }
    }

    function render() {
        const followingMonth = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth() + 1, 1);
        firstMonth.textContent = monthTitle.format(visibleMonth);
        secondMonth.textContent = monthTitle.format(followingMonth);
        renderMonth(visibleMonth, dayContainers[0]);
        renderMonth(followingMonth, dayContainers[1]);
    }

    function moveMonth(offset) {
        const next = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth() + offset, 1);
        if (next.getFullYear() < 1000 || next.getFullYear() > 2099) return;
        visibleMonth = next;
        render();
    }

    toggle.addEventListener('click', () => {
        if (!calendar.hidden) {
            close();
            return;
        }
        calendar.hidden = false;
        toggle.setAttribute('aria-expanded', 'true');
        render();
    });

    picker.querySelector('[data-range-prev-year]').addEventListener('click', () => moveMonth(-12));
    picker.querySelector('[data-range-prev-month]').addEventListener('click', () => moveMonth(-1));
    picker.querySelector('[data-range-next-month]').addEventListener('click', () => moveMonth(1));
    picker.querySelector('[data-range-next-year]').addEventListener('click', () => moveMonth(12));
    picker.querySelector('[data-range-clear]').addEventListener('click', () => {
        start = null;
        end = null;
        updateLabels();
        render();
        close(true);
    });
    picker.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !calendar.hidden) {
            event.preventDefault();
            close(true);
        }
    });
    document.addEventListener('click', (event) => {
        if (!picker.contains(event.target)) close();
    });

    updateLabels();
}

document.querySelectorAll('[data-futapay-date-range]').forEach(initializeDateRangePicker);
