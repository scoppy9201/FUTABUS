import assert from 'node:assert/strict';
import test from 'node:test';
import {
    calendarMonthDays, daysInMonth, isUiCalendarDayAllowed, isUiDateAllowed,
    parseUiDateValue, serializeUiDateValue, uiDateNumber, uiDatePrecision,
} from '../../packages/FuteBus/Core/src/resources/js/ui-date-value.js';

test('rejects invalid civil dates and distinguishes Gregorian leap years', () => {
    assert.equal(daysInMonth(2000, 2), 29);
    assert.equal(daysInMonth(1900, 2), 28);
    assert.ok(parseUiDateValue('date', '2024-02-29'));
    for (const value of ['2025-02-29', '2026-04-31', '2026-13-01', '2026-01-00', '0000-01-01', '12/10/2026']) {
        assert.equal(parseUiDateValue('date', value), null);
    }
    assert.equal(parseUiDateValue('month', '2026-10-12'), null);
    assert.equal(parseUiDateValue('date', '2026-10'), null);
    assert.equal(parseUiDateValue('text', '2026-10-12'), null);
});

test('round trips dates, months and literal local datetimes without changing their civil fields', () => {
    for (const [type, value, precision] of [
        ['date', '0001-01-01', 'minute'], ['date', '0099-12-31', 'minute'],
        ['month', '2026-10', 'minute'], ['datetime-local', '2026-03-08T02:30', 'minute'],
        ['datetime-local', '2026-10-12T20:30:15.125', 'millisecond'],
    ]) {
        assert.equal(serializeUiDateValue(type, parseUiDateValue(type, value), precision), value);
    }
    assert.equal(uiDateNumber('date', parseUiDateValue('date', '1970-01-01')), 0);
    assert.equal(new Date(uiDateNumber('date', parseUiDateValue('date', '0099-12-31'))).getUTCFullYear(), 99);
});

test('retains minute request format by default and supports explicit seconds and fractions', () => {
    const parts = parseUiDateValue('time', '20:30:15.2');
    assert.equal(serializeUiDateValue('time', parts), '20:30');
    assert.equal(serializeUiDateValue('time', parts, 'second'), '20:30:15');
    assert.equal(serializeUiDateValue('time', parts, 'millisecond'), '20:30:15.200');
    for (const value of ['24:00', '20:60', '20:30:60', '8:30', '20:30:15.1234']) {
        assert.equal(parseUiDateValue('time', value), null);
    }
    const input = (attributes) => ({ value: '', step: '', min: '', max: '', getAttribute: () => '', ...attributes });
    assert.equal(uiDatePrecision(input({ value: '20:30' })), 'minute');
    assert.equal(uiDatePrecision(input({ step: '1' })), 'second');
    assert.equal(uiDatePrecision(input({ min: '20:30:15.125' })), 'millisecond');
});

test('enforces date and month boundaries and the native minimum-based step', () => {
    const bounds = { min: '2026-10-12', max: '2026-10-20', step: '2' };
    assert.equal(isUiDateAllowed('date', '2026-10-12', bounds), true);
    assert.equal(isUiDateAllowed('date', '2026-10-20', bounds), true);
    assert.equal(isUiDateAllowed('date', '2026-10-13', bounds), false);
    assert.equal(isUiDateAllowed('date', '2026-10-10', bounds), false);
    assert.equal(isUiDateAllowed('date', '2026-10-22', bounds), false);
    assert.equal(isUiDateAllowed('month', '2026-12', { min: '2026-10', step: '2' }), true);
    assert.equal(isUiDateAllowed('month', '2026-11', { min: '2026-10', step: '2' }), false);
});

test('uses the initial value as step base when no valid minimum is supplied', () => {
    assert.equal(isUiDateAllowed('date', '2026-10-13', { baseValue: '2026-10-11', step: '2' }), true);
    assert.equal(isUiDateAllowed('date', '2026-10-12', { min: 'invalid', baseValue: '2026-10-11', step: '2' }), false);
    assert.equal(isUiDateAllowed('time', '20:31', { baseValue: '20:30', step: '120' }), false);
    assert.equal(isUiDateAllowed('time', '20:31:12', { step: 'any' }), true);
});

test('supports periodic overnight time bounds while rejecting the excluded daytime interval', () => {
    const bounds = { min: '23:00', max: '02:00' };
    for (const value of ['23:00', '23:59', '00:00', '02:00']) assert.equal(isUiDateAllowed('time', value, bounds), true);
    for (const value of ['02:01', '12:00', '22:59']) assert.equal(isUiDateAllowed('time', value, bounds), false);
    assert.equal(isUiDateAllowed('time', '00:00:00.125', { step: '0.125' }), true);
    assert.equal(isUiDateAllowed('time', '00:00:00.120', { step: '0.125' }), false);
});

test('allows a datetime day only if some selectable time exists within its bounds and step', () => {
    const day = (value) => parseUiDateValue('date', value);
    const bounds = { min: '2026-10-12T20:30', max: '2026-10-13T04:30' };
    assert.equal(isUiCalendarDayAllowed('datetime-local', day('2026-10-11'), bounds), false);
    assert.equal(isUiCalendarDayAllowed('datetime-local', day('2026-10-12'), bounds), true);
    assert.equal(isUiCalendarDayAllowed('datetime-local', day('2026-10-13'), bounds), true);
    assert.equal(isUiCalendarDayAllowed('datetime-local', day('2026-10-14'), bounds), false);
    assert.equal(isUiDateAllowed('datetime-local', '2026-10-12T20:29', bounds), false);
    assert.equal(isUiDateAllowed('datetime-local', '2026-10-13T04:31', bounds), false);
    const sparse = { min: '2026-10-12T12:00', max: '2026-10-15T12:00', step: String(2 * 86400) };
    assert.equal(isUiCalendarDayAllowed('datetime-local', day('2026-10-13'), sparse), false);
    assert.equal(isUiCalendarDayAllowed('datetime-local', day('2026-10-14'), sparse), true);
});

test('datetime calendars reject nonexistent civil days before checking their available time interval', () => {
    const base = parseUiDateValue('date', '2026-02-01');
    for (const parts of [{ ...base, day: 30 }, { ...base, year: 2025, day: 29 }, { ...base, month: 13 }, { ...base, day: 0 }]) {
        assert.equal(isUiCalendarDayAllowed('datetime-local', parts), false);
    }
    assert.equal(isUiCalendarDayAllowed('datetime-local', { ...base, year: 2024, day: 29 }), true);
});

test('datetime calendar boundary grids disable days outside the supported year range', () => {
    const firstYear = calendarMonthDays(1, 1);
    const lastYear = calendarMonthDays(9999, 12);
    for (const parts of [...firstYear, ...lastYear]) {
        assert.equal(isUiCalendarDayAllowed('datetime-local', parts), parts.year >= 1 && parts.year <= 9999);
    }
});

test('generates a complete Monday-first grid spanning month and year boundaries', () => {
    const days = calendarMonthDays(2026, 1);
    assert.equal(days.length, 42);
    assert.equal(serializeUiDateValue('date', days[0]), '2025-12-29');
    assert.equal(serializeUiDateValue('date', days[41]), '2026-02-08');
    for (let index = 1; index < days.length; index++) {
        assert.equal(uiDateNumber('date', days[index]) - uiDateNumber('date', days[index - 1]), 86400000);
    }
    assert.equal(calendarMonthDays(2024, 2).filter((parts) => parts.month === 2).length, 29);
});
