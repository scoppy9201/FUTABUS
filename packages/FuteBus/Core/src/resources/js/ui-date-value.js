const pad = (value, length = 2) => String(value).padStart(length, '0');
const datePattern = /^(\d{4})-(\d{2})(?:-(\d{2}))?$/;
const timePattern = /^(\d{2}):(\d{2})(?::(\d{2})(?:\.(\d{1,3}))?)?$/;

export function daysInMonth(year, month) {
    if (month === 2) return year % 4 === 0 && (year % 100 !== 0 || year % 400 === 0) ? 29 : 28;
    return [4, 6, 9, 11].includes(month) ? 30 : 31;
}

export function parseUiDateValue(type, value) {
    if (!['date', 'datetime-local', 'time', 'month'].includes(type) || typeof value !== 'string' || value === '') return null;
    const parts = { year: 1970, month: 1, day: 1, hour: 0, minute: 0, second: 0, millisecond: 0 };
    let dateValue = value;
    let timeValue = type === 'time' ? value : null;
    if (type === 'datetime-local') {
        const split = value.split('T');
        if (split.length !== 2) return null;
        [dateValue, timeValue] = split;
    }
    if (type !== 'time') {
        const match = datePattern.exec(dateValue);
        if (!match || (type === 'month' ? match[3] !== undefined : match[3] === undefined)) return null;
        parts.year = Number(match[1]);
        parts.month = Number(match[2]);
        parts.day = Number(match[3] ?? 1);
        if (parts.year < 1 || parts.year > 9999 || parts.month < 1 || parts.month > 12
            || parts.day < 1 || parts.day > daysInMonth(parts.year, parts.month)) return null;
    }
    if (timeValue !== null) {
        const match = timePattern.exec(timeValue);
        if (!match) return null;
        parts.hour = Number(match[1]);
        parts.minute = Number(match[2]);
        parts.second = Number(match[3] ?? 0);
        parts.millisecond = Number((match[4] ?? '').padEnd(3, '0'));
        if (parts.hour > 23 || parts.minute > 59 || parts.second > 59) return null;
    }
    return parts;
}

export function serializeUiDateValue(type, parts, precision = 'minute') {
    const date = `${pad(parts.year, 4)}-${pad(parts.month)}-${pad(parts.day)}`;
    let time = `${pad(parts.hour)}:${pad(parts.minute)}`;
    if (precision !== 'minute') time += `:${pad(parts.second)}`;
    if (precision === 'millisecond') time += `.${pad(parts.millisecond, 3)}`;
    const value = type === 'month' ? date.slice(0, 7)
        : type === 'time' ? time : type === 'datetime-local' ? `${date}T${time}` : date;
    return parseUiDateValue(type, value) ? value : '';
}

export function uiDateNumber(type, parts) {
    if (type === 'month') return (parts.year - 1970) * 12 + parts.month - 1;
    const time = ((parts.hour * 60 + parts.minute) * 60 + parts.second) * 1000 + parts.millisecond;
    if (type === 'time') return time;
    const civil = new Date(0);
    civil.setUTCFullYear(parts.year, parts.month - 1, parts.day);
    civil.setUTCHours(0, 0, 0, 0);
    return civil.getTime() + (type === 'datetime-local' ? time : 0);
}

function stepDetails(type, constraints) {
    const defaultStep = ['time', 'datetime-local'].includes(type) ? 60 : 1;
    const step = Number(constraints.step);
    const factor = type === 'date' ? 86400000 : type === 'month' ? 1 : 1000;
    const base = parseUiDateValue(type, constraints.min) ?? parseUiDateValue(type, constraints.baseValue);
    return {
        size: constraints.step === 'any' ? null : (Number.isFinite(step) && step > 0 ? step : defaultStep) * factor,
        base: base ? uiDateNumber(type, base) : 0,
    };
}

export function isUiDateAllowed(type, value, constraints = {}) {
    const parts = parseUiDateValue(type, value);
    if (!parts) return false;
    const number = uiDateNumber(type, parts);
    const minParts = parseUiDateValue(type, constraints.min);
    const maxParts = parseUiDateValue(type, constraints.max);
    const min = minParts ? uiDateNumber(type, minParts) : null;
    const max = maxParts ? uiDateNumber(type, maxParts) : null;
    if (type === 'time' && min !== null && max !== null && min > max) {
        if (number < min && number > max) return false;
    } else if ((min !== null && number < min) || (max !== null && number > max)) return false;
    const { size, base } = stepDetails(type, constraints);
    if (size === null) return true;
    const offset = (number - base) / size;
    return Math.abs(offset - Math.round(offset)) < 0.000001;
}

export function isUiCalendarDayAllowed(type, parts, constraints = {}) {
    if (type !== 'datetime-local') return isUiDateAllowed(type, serializeUiDateValue(type, parts), constraints);
    if (!serializeUiDateValue('date', parts)) return false;
    const start = uiDateNumber(type, { ...parts, hour: 0, minute: 0, second: 0, millisecond: 0 });
    const min = parseUiDateValue(type, constraints.min);
    const max = parseUiDateValue(type, constraints.max);
    const lower = Math.max(start, min ? uiDateNumber(type, min) : start);
    const upper = Math.min(start + 86399999, max ? uiDateNumber(type, max) : start + 86399999);
    if (lower > upper) return false;
    const { size, base } = stepDetails(type, constraints);
    return size === null || base + Math.ceil((lower - base) / size) * size <= upper;
}

export function uiDatePrecision(input) {
    const step = Number(input.step);
    const values = [input.value, input.min, input.max, input.getAttribute?.('value') ?? ''];
    if ((step > 0 && step < 1) || values.some((value) => /:\d{2}\.\d+$/.test(value))) return 'millisecond';
    if ((step > 0 && step < 60) || values.some((value) => /:\d{2}:\d{2}$/.test(value))) return 'second';
    return 'minute';
}

export function uiDateFromParts(parts) {
    const date = new Date(0);
    date.setFullYear(parts.year, parts.month - 1, parts.day);
    date.setHours(parts.hour, parts.minute, parts.second, parts.millisecond);
    return date;
}

export function calendarMonthDays(year, month) {
    const first = uiDateFromParts({ year, month, day: 1, hour: 12, minute: 0, second: 0, millisecond: 0 });
    const offset = (first.getDay() + 6) % 7;
    return Array.from({ length: 42 }, (_, index) => {
        const date = new Date(first);
        date.setDate(1 - offset + index);
        return { year: date.getFullYear(), month: date.getMonth() + 1, day: date.getDate(), hour: 0, minute: 0, second: 0, millisecond: 0 };
    });
}
