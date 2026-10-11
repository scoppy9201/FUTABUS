import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import { parseVoiceTripSearch } from '../../packages/FuteBus/Core/src/resources/js/voice-trip-search-parser.js';

const catalog = JSON.parse(readFileSync(new URL('../../app/Data/futa_booking_locations.json', import.meta.url), 'utf8'));
const today = '2026-10-11';

test('recognizes a Vietnamese one-way trip and fills actual catalog locations', () => {
    const result = parseVoiceTripSearch('Từ Sài Gòn đi Đà Lạt ngày mai 2 vé', catalog, today);
    assert.deepEqual(result, {
        departure: 'TP. Hồ Chí Minh',
        destination: 'Đà Lạt',
        departureDate: '2026-10-12',
        returnDate: null,
        roundTrip: false,
        quantity: 2,
        error: null,
    });
});

test('recognizes round trip dates without submitting a search', () => {
    const result = parseVoiceTripSearch('Từ TP Hồ Chí Minh đến Lâm Đồng ngày 15 tháng 10 khứ hồi ngày về 20 tháng 10', catalog, today);
    assert.equal(result.departureDate, '2026-10-15');
    assert.equal(result.returnDate, '2026-10-20');
    assert.equal(result.roundTrip, true);
    assert.equal(result.error, null);
});

test('recognizes the English example shown in the interface', () => {
    const result = parseVoiceTripSearch('From Saigon to Da Lat tomorrow, 2 tickets', catalog, today);
    assert.equal(result.departure, 'TP. Hồ Chí Minh');
    assert.equal(result.destination, 'Đà Lạt');
    assert.equal(result.departureDate, '2026-10-12');
    assert.equal(result.quantity, 2);
    assert.equal(result.error, null);
});

test('understands natural Vietnamese speech without route keywords or digit quantity', () => {
    const result = parseVoiceTripSearch('Cho tôi hai vé Sài Gòn Đà Lạt ngày mai', catalog, today);
    assert.equal(result.departure, 'TP. Hồ Chí Minh');
    assert.equal(result.destination, 'Đà Lạt');
    assert.equal(result.departureDate, '2026-10-12');
    assert.equal(result.quantity, 2);
    assert.equal(result.error, null);
});

test('understands spoken dates, weekdays, and day of month', () => {
    const route = 'Sài Gòn Đà Lạt ';
    assert.equal(parseVoiceTripSearch(route + 'ngày mười hai tháng mười', catalog, today).departureDate, '2026-10-12');
    assert.equal(parseVoiceTripSearch(route + 'thứ hai tuần sau', catalog, today).departureDate, '2026-10-12');
    assert.equal(parseVoiceTripSearch(route + 'ngày 15', catalog, today).departureDate, '2026-10-15');
    assert.equal(parseVoiceTripSearch(route + 'ngày 9', catalog, today).departureDate, '2026-11-09');
    assert.equal(parseVoiceTripSearch(route + 'ngày mốt', catalog, today).departureDate, '2026-10-13');
    const roundTrip = parseVoiceTripSearch(route + 'ngày mười hai tháng mười khứ hồi ngày mười lăm tháng mười', catalog, today);
    assert.equal(roundTrip.departureDate, '2026-10-12');
    assert.equal(roundTrip.returnDate, '2026-10-15');
    assert.equal(roundTrip.error, null);
});

test('does not silently search the current form date when a spoken date cannot be understood', () => {
    assert.equal(parseVoiceTripSearch('Sài Gòn Đà Lạt ngày ba mươi mốt tháng hai', catalog, today).error, 'date');
    assert.equal(parseVoiceTripSearch('Sài Gòn Đà Lạt ngày cuối tháng', catalog, today).error, 'date');
});

test('recognizes ticket counts in common spoken forms and rejects more than five', () => {
    const route = 'Sài Gòn Đà Lạt ngày mai ';
    assert.equal(parseVoiceTripSearch(route + 'ba vé', catalog, today).quantity, 3);
    assert.equal(parseVoiceTripSearch(route + 'số vé là 4', catalog, today).quantity, 4);
    assert.equal(parseVoiceTripSearch(route + '6 vé', catalog, today).error, 'quantity');
    assert.equal(parseVoiceTripSearch(route + 'sáu vé', catalog, today).error, 'quantity');
    assert.equal(parseVoiceTripSearch(route + 'mười vé', catalog, today).error, 'quantity');
    assert.equal(parseVoiceTripSearch(route + 'không vé', catalog, today).error, 'quantity');
});

test('rejects unknown places, missing return date, and invalid quantity', () => {
    assert.equal(parseVoiceTripSearch('Từ Sao Hỏa đi Đà Lạt ngày mai', catalog, today).error, 'location');
    assert.equal(parseVoiceTripSearch('Từ Sài Gòn đi Đà Lạt ngày mai khứ hồi', catalog, today).error, 'return_date');
    assert.equal(parseVoiceTripSearch('Từ Sài Gòn đi Đà Lạt ngày mai 9 vé', catalog, today).error, 'quantity');
    assert.equal(parseVoiceTripSearch('Từ Sài Gòn đi Đà Lạt ngày 31/02/2027', catalog, today).error, 'date');
    assert.equal(parseVoiceTripSearch('Từ Sài Gòn đi Đà Lạt ngày 20/10 khứ hồi ngày về 15/10', catalog, today).error, 'date');
});
