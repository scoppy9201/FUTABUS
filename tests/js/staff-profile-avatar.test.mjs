import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

test('header avatar is notified only after a successful profile save', async () => {
    const events = [];
    let rejectSave = true;
    const window = {
        axios: { post: async (url, body, options) => {
            assert.equal(options.headers['X-CSRF-TOKEN'], 'test-csrf');
            assert.equal(options.headers.Accept, 'application/json');
            if (rejectSave) throw { response: { status: 422, data: { errors: { avatar: ['Invalid'] } } } };
            return { data: { data: { name: 'Bùi Phương', has_avatar: true } } };
        } },
        FutaNotify: { success() {}, error() {} },
    };
    const source = fs.readFileSync(new URL('../../packages/FuteBus/StaffDashboard/src/resources/js/staff-profile.js', import.meta.url), 'utf8');
    vm.runInNewContext(source, { window, document: { documentElement: { lang: 'vi' } },
        FormData: class { set() {} }, URL, Date });
    const state = window.staffProfile({ update: '/profile', avatar: '/avatar', csrf: 'test-csrf' }, { validation: {} });
    state.employee = { name: 'Old' };
    state.validate = () => {};
    state.$refs = { form: {}, avatar: { value: 'selected.png' } };
    state.$dispatch = (name, detail) => events.push({ name, detail });
    await state.save();
    assert.equal(events.length, 0);
    rejectSave = false;
    state.errors = {};
    await state.save();
    assert.equal(events.length, 1);
    assert.equal(events[0].name, 'staff-profile-saved');
    assert.equal(events[0].detail.name, 'Bùi Phương');
    assert.match(events[0].detail.avatar_url, /^\/avatar\?v=\d+$/);
});

test('save errors distinguish an expired session, forbidden access, upload size, server and network failures', async () => {
    const source = fs.readFileSync(new URL('../../packages/FuteBus/StaffDashboard/src/resources/js/staff-profile.js', import.meta.url), 'utf8');
    for (const [status, expected] of [[419, 'session'], [401, 'session'], [403, 'forbidden'], [413, 'large'], [500, 'server'], [null, 'network']]) {
        const window = { axios: { post: async () => { throw status ? { response: { status, data: {} } } : new Error('Network'); } },
            FutaNotify: { error() {} } };
        vm.runInNewContext(source, { window, document: { documentElement: { lang: 'vi' } }, FormData: class { set() {} } });
        const state = window.staffProfile({ update: '/profile', csrf: 'token' }, { validation: {}, sessionExpired: 'session',
            forbidden: 'forbidden', uploadTooLarge: 'large', saveError: 'server', networkError: 'network' });
        state.employee = {};
        state.validate = () => {};
        state.$refs = { form: {} };
        await state.save();
        assert.equal(state.message, expected);
        assert.equal(state.saving, false);
    }
});
