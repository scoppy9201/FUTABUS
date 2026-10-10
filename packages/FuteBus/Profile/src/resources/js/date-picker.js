function parseDate(value) {
    const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value);

    if (!match) return null;

    const date = new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]));

    return date.getFullYear() === Number(match[1])
        && date.getMonth() === Number(match[2]) - 1
        && date.getDate() === Number(match[3])
        ? date
        : null;
}

function formatValue(date) {
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
}

function initializeDatePicker(picker) {
    if (picker.dataset.initialized) return;
    picker.dataset.initialized = 'true';

    const input = picker.querySelector('[data-profile-date-value]');
    const toggle = picker.querySelector('[data-profile-date-toggle]');
    const label = picker.querySelector('[data-profile-date-label]');
    const calendar = picker.querySelector('[data-profile-calendar]');
    const monthLabel = picker.querySelector('[data-profile-date-month]');
    const yearInput = picker.querySelector('[data-profile-date-year]');
    const weekdays = picker.querySelector('[data-profile-date-weekdays]');
    const days = picker.querySelector('[data-profile-date-days]');
    const previous = picker.querySelector('[data-profile-date-previous]');
    const next = picker.querySelector('[data-profile-date-next]');
    const clear = picker.querySelector('[data-profile-date-clear]');
    const maximum = picker.dataset.max ? parseDate(picker.dataset.max) : null;
    const reference = maximum ?? new Date();
    const today = new Date();
    const locale = document.documentElement.lang === 'vi' ? 'vi-VN' : 'en-US';
    const monthFormatter = new Intl.DateTimeFormat(locale, { month: 'long' });
    const dateFormatter = new Intl.DateTimeFormat(locale, { day: '2-digit', month: '2-digit', year: 'numeric' });
    const fullDateFormatter = new Intl.DateTimeFormat(locale, { dateStyle: 'full' });
    const weekdayFormatter = new Intl.DateTimeFormat(locale, { weekday: 'short' });
    const placeholder = label.textContent.trim();
    let selected = parseDate(input.value);
    let visibleYear = (selected ?? reference).getFullYear();
    let visibleMonth = (selected ?? reference).getMonth();

    for (let day = 0; day < 7; day++) {
        const heading = document.createElement('span');
        heading.textContent = weekdayFormatter.format(new Date(2023, 0, day + 2));
        weekdays.append(heading);
    }

    function close() {
        calendar.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
    }

    function updateLabel() {
        label.textContent = selected ? dateFormatter.format(selected) : placeholder;
    }

    function render() {
        monthLabel.textContent = monthFormatter.format(new Date(visibleYear, visibleMonth, 1));
        yearInput.value = visibleYear;
        previous.disabled = visibleYear === 1000 && visibleMonth === 0;
        next.disabled = maximum !== null
            ? new Date(visibleYear, visibleMonth + 1, 1) > maximum
            : visibleYear === 2100 && visibleMonth === 11;
        days.replaceChildren();

        const firstWeekday = (new Date(visibleYear, visibleMonth, 1).getDay() + 6) % 7;

        for (let blank = 0; blank < firstWeekday; blank++) {
            days.append(document.createElement('span'));
        }

        const count = new Date(visibleYear, visibleMonth + 1, 0).getDate();

        for (let day = 1; day <= count; day++) {
            const date = new Date(visibleYear, visibleMonth, day);
            const button = document.createElement('button');
            const isSelected = selected && formatValue(date) === formatValue(selected);
            const isToday = formatValue(date) === formatValue(today);

            button.type = 'button';
            button.textContent = day;
            button.setAttribute('aria-label', fullDateFormatter.format(date));
            button.disabled = maximum !== null && date > maximum;
            button.className = 'grid size-9 place-items-center rounded-full text-sm font-medium focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-futa-orange';

            if (isSelected) {
                button.className += ' bg-futa-orange font-semibold text-white hover:bg-futa-orange-dark';
                button.setAttribute('aria-pressed', 'true');
            } else if (button.disabled) {
                button.className += ' cursor-not-allowed text-gray-300';
            } else if (isToday) {
                button.className += ' text-futa-orange ring-1 ring-futa-orange hover:bg-futa-orange-soft';
            } else {
                button.className += ' text-gray-900 hover:bg-futa-orange-soft hover:text-futa-orange';
            }

            button.addEventListener('click', () => {
                selected = date;
                input.value = formatValue(date);
                updateLabel();
                close();
                toggle.focus();
            });
            days.append(button);
        }
    }

    toggle.addEventListener('click', () => {
        calendar.hidden = !calendar.hidden;
        toggle.setAttribute('aria-expanded', String(!calendar.hidden));
        if (!calendar.hidden) render();
    });

    function changeMonth(offset) {
        const date = new Date(visibleYear, visibleMonth + offset, 1);
        visibleYear = date.getFullYear();
        visibleMonth = date.getMonth();
        render();
    }

    previous.addEventListener('click', () => changeMonth(-1));
    next.addEventListener('click', () => changeMonth(1));

    yearInput.addEventListener('input', () => {
        const year = Number(yearInput.value);

        const lastYear = maximum?.getFullYear() ?? 2100;

        if (yearInput.value.length === 4 && Number.isInteger(year) && year >= 1000 && year <= lastYear) {
            visibleYear = year;
            if (maximum !== null && new Date(year, visibleMonth, 1) > maximum) visibleMonth = maximum.getMonth();
            render();
        }
    });
    yearInput.addEventListener('change', () => {
        yearInput.value = visibleYear;
    });

    clear.addEventListener('click', () => {
        selected = null;
        input.value = '';
        updateLabel();
        close();
        toggle.focus();
    });

    document.addEventListener('click', (event) => {
        if (!picker.contains(event.target)) close();
    });
    picker.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            close();
            toggle.focus();
        }
    });
    input.form.addEventListener('reset', () => queueMicrotask(() => {
        selected = parseDate(input.value);
        visibleYear = (selected ?? reference).getFullYear();
        visibleMonth = (selected ?? reference).getMonth();
        updateLabel();
        close();
    }));

    updateLabel();
}

function initializeProfileDatePickers() {
    document.querySelectorAll('[data-profile-date-picker]').forEach(initializeDatePicker);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeProfileDatePickers, { once: true });
} else {
    initializeProfileDatePickers();
}
