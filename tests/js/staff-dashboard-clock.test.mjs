import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

test('dashboard clock advances without reload, catches up after backgrounding and removes its timer', async () => {
    let current = '2026-10-14T00:30:00Z';
    let tick;
    let cleared = false;
    const events = {};
    const window = {
        axios: { get: async () => ({ data: { data: { employee: {}, groups: {} } } }) },
        setInterval(callback) { tick = callback; return 7; },
        clearInterval(id) { cleared = id === 7; },
        addEventListener(name, callback) { events[name] = callback; },
        removeEventListener(name) { delete events[name]; },
    };
    const document = {
        documentElement: { lang: 'vi' },
        addEventListener(name, callback) { events[name] = callback; },
        removeEventListener(name) { delete events[name]; },
    };
    const context = { window, document, Intl, Date: class extends Date { constructor() { super(current); } } };
    vm.runInNewContext(fs.readFileSync(new URL('../../packages/FuteBus/StaffDashboard/src/resources/js/staff-dashboard.js', import.meta.url), 'utf8'), context);
    const dashboard = window.staffDashboard('/dashboard');
    await dashboard.init();
    assert.equal(dashboard.timeLabel, '07:30');
    current = '2026-10-14T00:48:00Z';
    tick();
    assert.equal(dashboard.timeLabel, '07:48');
    current = '2026-10-14T17:01:00Z';
    events.visibilitychange();
    assert.equal(dashboard.timeLabel, '00:01');
    assert.match(dashboard.dateLabel, /15/);
    dashboard.destroy();
    assert.equal(cleared, true);
    assert.deepEqual(events, {});
});
