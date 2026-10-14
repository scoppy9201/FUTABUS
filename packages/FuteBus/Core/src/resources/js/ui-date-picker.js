import { createControl, dispatchControlChange, observeControl } from './ui-control.js';
import { createPopup } from './ui-popup.js';
import {
    calendarMonthDays, daysInMonth, isUiCalendarDayAllowed, isUiDateAllowed, parseUiDateValue,
    serializeUiDateValue, uiDateFromParts, uiDateNumber, uiDatePrecision,
} from './ui-date-value.js';

const supportedTypes = new Set(['date', 'datetime-local', 'time', 'month']);
const dateControllers = new Map();
const locale = document.documentElement.lang === 'vi' ? 'vi-VN' : 'en-GB';
const monthFormatter = new Intl.DateTimeFormat(locale, { month: 'long' });
const shortMonthFormatter = new Intl.DateTimeFormat(locale, { month: 'short' });
const fullDateFormatter = new Intl.DateTimeFormat(locale, { dateStyle: 'full' });
const weekdayFormatter = new Intl.DateTimeFormat(locale, { weekday: 'short' });

function localNowParts() {
    const now = new Date();
    return {
        year: now.getFullYear(), month: now.getMonth() + 1, day: now.getDate(),
        hour: now.getHours(), minute: now.getMinutes(), second: now.getSeconds(), millisecond: now.getMilliseconds(),
    };
}

function initializeDateControl(source) {
    if (source.dataset.uiDate || source.hasAttribute('data-ui-date-native') || !supportedTypes.has(source.type)) return;
    const control = createControl(source, '#futa-ui-calendar-template');
    if (!control) return;
    source.dataset.uiDate = 'ready';
    const { root, trigger, label, panel, error } = control;
    const type = source.type;
    const includesTime = type === 'time' || type === 'datetime-local';
    const elements = Object.fromEntries([
        'calendar', 'previous', 'next', 'month', 'year', 'weekdays', 'days', 'months', 'time', 'today', 'clear', 'apply', 'status',
    ].map((key) => [key, panel.querySelector(`[data-ui-date-${key}]`)]));
    const timeInputs = [...panel.querySelectorAll('[data-ui-time-value]')];
    let draft = localNowParts();
    let viewYear = draft.year;
    let viewMonth = draft.month;
    let precision = uiDatePrecision(source);

    const constraints = () => ({
        min: source.min, max: source.max, step: source.step, baseValue: source.getAttribute('value'),
    });
    const candidate = () => serializeUiDateValue(type, draft, precision);
    const sameDate = (left, right) => left && right
        && left.year === right.year && left.month === right.month && (type === 'month' || left.day === right.day);

    function startingParts() {
        const current = parseUiDateValue(type, source.value) ?? localNowParts();
        const bounds = constraints();
        const min = parseUiDateValue(type, bounds.min);
        const max = parseUiDateValue(type, bounds.max);
        const number = uiDateNumber(type, current);
        if (type === 'time' && min && max && uiDateNumber(type, min) > uiDateNumber(type, max)) {
            return isUiDateAllowed(type, serializeUiDateValue(type, current, precision), bounds) ? current : min;
        }
        if (min && number < uiDateNumber(type, min)) return min;
        if (max && number > uiDateNumber(type, max)) return max;
        return current;
    }

    function displayValue() {
        const value = parseUiDateValue(type, source.value);
        if (!value) return source.placeholder || panel.dataset.choose;
        if (type === 'time') return serializeUiDateValue('time', value, uiDatePrecision(source));
        const options = type === 'month' ? { month: 'long', year: 'numeric' }
            : { day: '2-digit', month: '2-digit', year: 'numeric' };
        if (type === 'datetime-local') {
            options.hour = '2-digit';
            options.minute = '2-digit';
            options.hour12 = false;
            if (uiDatePrecision(source) !== 'minute') options.second = '2-digit';
        }
        options.timeZone = 'UTC';
        return new Intl.DateTimeFormat(locale, options).format(new Date(uiDateNumber('datetime-local', value)));
    }

    function syncTrigger() {
        label.textContent = displayValue();
        label.classList.toggle('is-placeholder', !source.value);
        trigger.disabled = source.disabled || source.readOnly;
        trigger.setAttribute('aria-required', String(source.required));
        trigger.setAttribute('aria-invalid', String(source.getAttribute('aria-invalid') === 'true' || !error.hidden));
        if (trigger.disabled) popup.close();
        if (popup.isOpen()) {
            precision = uiDatePrecision(source);
            draft = startingParts();
            viewYear = draft.year;
            viewMonth = draft.month;
            render();
        }
    }

    function updateApply() {
        const timeIsValid = !includesTime || timeInputs.every((input) => input.closest('[hidden]')
            || (input.value !== '' && Number.isInteger(Number(input.value)) && input.validity.valid));
        const allowed = timeIsValid && isUiDateAllowed(type, candidate(), constraints());
        elements.apply.disabled = !allowed;
        elements.status.hidden = allowed;
        elements.status.textContent = allowed ? '' : panel.dataset.invalidDate;
    }

    function commit(value) {
        if (source.disabled || source.readOnly || (value && !isUiDateAllowed(type, value, constraints()))) return;
        dispatchControlChange(source, value);
        popup.close(true);
    }

    function selectParts(parts) {
        draft = { ...draft, year: parts.year, month: parts.month, day: parts.day };
        viewYear = parts.year;
        viewMonth = parts.month;
        if (!includesTime) commit(candidate());
        else {
            timeInputs[0].focus({ preventScroll: true });
            render();
        }
    }

    function renderDays() {
        elements.days.replaceChildren();
        const dates = calendarMonthDays(viewYear, viewMonth);
        const current = parseUiDateValue(type, source.value);
        const highlighted = includesTime ? draft : current;
        const today = localNowParts();
        let focusButton = null;
        for (const parts of dates) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'futa-calendar-day';
            button.textContent = String(parts.day);
            button.dataset.uiDateItem = serializeUiDateValue('date', parts);
            button.setAttribute('aria-label', fullDateFormatter.format(uiDateFromParts(parts)));
            button.setAttribute('aria-pressed', String(Boolean(sameDate(highlighted, parts))));
            button.classList.toggle('is-outside', parts.month !== viewMonth);
            button.classList.toggle('is-today', Boolean(sameDate(today, parts)));
            button.disabled = !isUiCalendarDayAllowed(type, parts, constraints());
            button.tabIndex = -1;
            if (!button.disabled && parts.month === viewMonth && (!focusButton || sameDate(highlighted, parts))) focusButton = button;
            button.addEventListener('click', () => selectParts(parts));
            elements.days.append(button);
        }
        if (focusButton) focusButton.tabIndex = 0;
    }

    function renderMonths() {
        elements.months.replaceChildren();
        const current = parseUiDateValue(type, source.value);
        for (let month = 1; month <= 12; month++) {
            const parts = { ...draft, year: viewYear, month, day: 1 };
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'futa-calendar-month';
            button.textContent = shortMonthFormatter.format(uiDateFromParts(parts));
            button.setAttribute('aria-label', `${monthFormatter.format(uiDateFromParts(parts))} ${viewYear}`);
            button.setAttribute('aria-pressed', String(Boolean(sameDate(current, parts))));
            button.disabled = !isUiDateAllowed(type, serializeUiDateValue(type, parts), constraints());
            button.addEventListener('click', () => selectParts(parts));
            elements.months.append(button);
        }
    }

    function canMove(offset) {
        const target = uiDateFromParts({ ...draft, year: viewYear, month: viewMonth, day: 1 });
        target.setMonth(target.getMonth() + offset);
        const year = target.getFullYear();
        const month = target.getMonth() + 1;
        if (year < 1 || year > 9999) return false;
        const min = parseUiDateValue(type, source.min);
        const max = parseUiDateValue(type, source.max);
        const index = year * 12 + month;
        return (!min || index >= min.year * 12 + min.month) && (!max || index <= max.year * 12 + max.month);
    }

    function render() {
        elements.calendar.hidden = type === 'time';
        elements.weekdays.hidden = type === 'month';
        elements.days.hidden = type === 'month';
        elements.months.hidden = type !== 'month';
        elements.time.hidden = !includesTime;
        elements.apply.hidden = !includesTime;
        elements.month.textContent = type === 'month' ? panel.dataset.monthLabel
            : monthFormatter.format(uiDateFromParts({ ...draft, year: viewYear, month: viewMonth, day: 1 }));
        elements.year.value = viewYear;
        const min = parseUiDateValue(type, source.min);
        const max = parseUiDateValue(type, source.max);
        elements.previous.disabled = type === 'month' ? viewYear <= (min?.year ?? 1) : !canMove(-1);
        elements.next.disabled = type === 'month' ? viewYear >= (max?.year ?? 9999) : !canMove(1);
        if (type === 'month') renderMonths();
        else if (type !== 'time') renderDays();
        for (const input of timeInputs) {
            const part = input.dataset.uiTimeValue;
            input.parentElement.hidden = part === 'second' ? precision === 'minute' : part === 'millisecond' && precision !== 'millisecond';
            input.value = String(draft[part]).padStart(part === 'millisecond' ? 3 : 2, '0');
        }
        elements.today.textContent = type === 'month' ? panel.dataset.currentMonth
            : includesTime ? panel.dataset.now : panel.dataset.today;
        const now = localNowParts();
        elements.today.disabled = !isUiDateAllowed(type, serializeUiDateValue(type, now, precision), constraints());
        updateApply();
    }

    function moveMonth(offset) {
        if (type === 'month') viewYear += offset;
        else {
            const target = uiDateFromParts({ ...draft, year: viewYear, month: viewMonth, day: 1 });
            target.setMonth(target.getMonth() + offset);
            viewYear = target.getFullYear();
            viewMonth = target.getMonth() + 1;
        }
        render();
    }

    const popup = createPopup(panel, trigger, {
        matchWidth: false,
        onOpen() {
            precision = uiDatePrecision(source);
            draft = startingParts();
            viewYear = draft.year;
            viewMonth = draft.month;
            render();
            queueMicrotask(() => (type === 'time' ? timeInputs[0]
                : panel.querySelector('[aria-pressed="true"]:not(:disabled), [data-ui-date-item][tabindex="0"]'))?.focus({ preventScroll: true }));
        },
    });

    for (let index = 0; index < 7; index++) {
        const heading = document.createElement('span');
        heading.textContent = weekdayFormatter.format(new Date(2023, 0, index + 2));
        elements.weekdays.append(heading);
    }
    root.querySelector('[data-ui-date-icon]').hidden = type === 'time';
    root.querySelector('[data-ui-time-icon]').hidden = type !== 'time';
    trigger.addEventListener('click', () => { if (!trigger.disabled) popup.toggle(); });
    elements.previous.addEventListener('click', () => moveMonth(-1));
    elements.next.addEventListener('click', () => moveMonth(1));
    elements.year.addEventListener('change', () => {
        const year = Number(elements.year.value);
        if (Number.isInteger(year) && year >= 1 && year <= 9999) viewYear = year;
        render();
    });
    elements.clear.addEventListener('click', () => commit(''));
    elements.apply.addEventListener('click', () => { if (!elements.apply.disabled) commit(candidate()); });
    elements.today.addEventListener('click', () => {
        draft = localNowParts();
        viewYear = draft.year;
        viewMonth = draft.month;
        if (!includesTime) commit(candidate());
        else render();
    });
    for (const input of timeInputs) {
        input.addEventListener('input', () => {
            const number = Number(input.value);
            if (input.value !== '' && Number.isInteger(number) && input.validity.valid) draft[input.dataset.uiTimeValue] = number;
            updateApply();
        });
    }
    panel.addEventListener('keydown', (event) => {
        if (includesTime && event.key === 'Enter' && event.target.matches('[data-ui-time-value]')) {
            event.preventDefault();
            if (!elements.apply.disabled) commit(candidate());
            return;
        }
        const button = event.target.closest('[data-ui-date-item]');
        if (!button || !['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End', 'PageUp', 'PageDown'].includes(event.key)) return;
        event.preventDefault();
        const parts = parseUiDateValue('date', button.dataset.uiDateItem);
        const date = uiDateFromParts(parts);
        const weekday = (date.getDay() + 6) % 7;
        if (event.key.startsWith('Page')) {
            const day = parts.day;
            date.setDate(1);
            date.setMonth(date.getMonth() + (event.key === 'PageUp' ? -1 : 1));
            date.setDate(Math.min(day, daysInMonth(date.getFullYear(), date.getMonth() + 1)));
        } else {
            const offset = { ArrowLeft: -1, ArrowRight: 1, ArrowUp: -7, ArrowDown: 7, Home: -weekday, End: 6 - weekday }[event.key];
            date.setDate(date.getDate() + offset);
        }
        const next = { ...parts, year: date.getFullYear(), month: date.getMonth() + 1, day: date.getDate() };
        if (!isUiCalendarDayAllowed(type, next, constraints())) return;
        viewYear = next.year;
        viewMonth = next.month;
        render();
        const target = panel.querySelector(`[data-ui-date-item="${serializeUiDateValue('date', next)}"]`);
        elements.days.querySelectorAll('button').forEach((item) => { item.tabIndex = item === target ? 0 : -1; });
        target?.focus({ preventScroll: true });
    });
    const stopObserving = observeControl(source, syncTrigger);
    dateControllers.set(source, { popup, stopObserving });
    syncTrigger();
}

export function initializeUiDatePickers(root = document) {
    for (const [source, controller] of dateControllers) {
        if (source.isConnected) continue;
        controller.popup.destroy();
        controller.stopObserving();
        dateControllers.delete(source);
    }
    const selector = 'input[type="date"], input[type="datetime-local"], input[type="time"], input[type="month"]';
    if (root.matches?.(selector)) initializeDateControl(root);
    root.querySelectorAll?.(selector).forEach(initializeDateControl);
}
