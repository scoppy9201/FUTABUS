import test from 'node:test';
import assert from 'node:assert/strict';
import { staffActivity } from '../../packages/FuteBus/StaffDashboard/src/resources/js/staff-activity.js';

const payload = (date, queue = []) => ({ data: { data: {
    days: [{ date, issued: 2, cancelled: 1, support_pending: 1, refund_pending: 1 }],
    queue, updated_at: `${date}T12:00:00+07:00`,
} } });

function fixture(get = async () => payload('2026-10-01')) {
    let timer;
    let cleared;
    globalThis.document = { hidden: false, documentElement: { lang: 'vi' } };
    globalThis.window = {
        axios: { get },
        setInterval(callback, delay) { timer = { callback, delay }; return 42; },
        clearInterval(id) { cleared = id; },
    };
    const activity = staffActivity('/activity');
    return { activity, tick: () => timer.callback(), timer: () => timer, cleared: () => cleared };
}

test('calendar aligns Monday first, crosses year boundaries and opens the selected day panel', async () => {
    const { activity } = fixture();
    activity.activityMonth = '2026-10';
    await activity.loadActivity();
    assert.equal(activity.activityCells.length, 4);
    assert.equal(activity.activityCells[0].date, null);
    assert.equal(activity.activityCells[3].date, '2026-10-01');
    assert.equal(activity.activityPending(activity.activityDays[0]), 2);
    let opened = false;
    activity.$refs = { activityPanel: { showModal() { opened = true; } } };
    activity.$nextTick = (callback) => callback();
    activity.openActivityDay(activity.activityDays[0]);
    assert.equal(opened, true);
    assert.equal(activity.activitySelectedDay.issued, 2);
    activity.activityMonth = '2026-12';
    await activity.changeActivityMonth(1);
    assert.equal(activity.activityMonth, '2027-01');
});

test('polling pauses in background tabs and is disposed when the dashboard closes', async () => {
    let calls = 0;
    let signal;
    const state = fixture(async (_url, options) => { calls++; signal = options.signal; return payload('2026-10-01'); });
    await state.activity.initActivity();
    assert.equal(state.timer().delay, 15000);
    assert.equal(calls, 1);
    document.hidden = true;
    state.tick();
    assert.equal(calls, 1);
    document.hidden = false;
    state.tick();
    assert.equal(calls, 2);
    state.activity.destroyActivity();
    assert.equal(state.cleared(), 42);
    assert.equal(signal.aborted, true);
});

test('a delayed response from an older month cannot overwrite the current month', async () => {
    const pending = [];
    const { activity } = fixture((_url, options) => new Promise((resolve) => pending.push({ resolve, signal: options.signal })));
    activity.activityMonth = '2026-10';
    const older = activity.loadActivity();
    const newer = activity.changeActivityMonth(1);
    assert.equal(pending[0].signal.aborted, true);
    pending[1].resolve(payload('2026-11-01'));
    await newer;
    pending[0].resolve(payload('2026-10-01'));
    await older;
    assert.equal(activity.activityDays[0].date, '2026-11-01');
    assert.equal(activity.activityLoading, false);
});

test('a refresh failure keeps the last queue and marks its data as stale', async () => {
    let fail = false;
    const { activity } = fixture(async () => {
        if (fail) throw new Error('offline');
        return payload('2026-10-01', [{ id: 'support-1' }]);
    });
    await activity.loadActivity();
    fail = true;
    await activity.loadActivity();
    assert.equal(activity.activityFailed, true);
    assert.equal(activity.activityQueue[0].id, 'support-1');
    assert.equal(activity.activityLoading, false);
});
