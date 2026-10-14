import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const source = fs.readFileSync(new URL('../../packages/FuteBus/StaffCustomerSupport/src/resources/js/staff-support.js', import.meta.url), 'utf8');
function setup(axios, confirm) {
    let keys = 0;
    const window = { axios, crypto: { randomUUID: () => `key-${++keys}` }, FutaNotify: { success() {}, show() {} } };
    if (confirm) window.FutaConfirm = confirm;
    vm.runInNewContext(source, { window, document: { documentElement: { lang: 'vi' } }, Intl, Date });
    const component = window.staffSupport({
        list: '/list', detail: '/detail/__report__', respond: '/respond/__report__',
        transfer: '/transfer/__report__', note: '/note/__report__', csrf: 'csrf',
    }, {
        error: 'error', saved: 'saved', response_required: 'Response required',
        note_required: 'Note required', departmentRole_required: 'Department required',
        transferReason_required: 'Reason required', too_long: 'Too long',
    });
    component.detail = { id: 4, version: 1, status: 'reviewing', is_handler: true };
    component.response = 'Customer response';
    return component;
}

test('an uncertain response retries the same key and clears it only after success', async () => {
    const sent = [];
    const component = setup({
        async post(url, body) {
            sent.push(body.idempotency_key);
            if (sent.length === 1) throw new Error('connection lost');
        },
        async get(url) {
            return { data: url === '/list' ? { data: [], meta: { current_page: 1 } } : { data: { id: 4, version: 2 } } };
        },
    });
    await component.respond();
    assert.equal(component.response, 'Customer response');
    assert.ok(component.pendingRequest);
    await component.respond();
    assert.deepEqual(sent, ['key-1', 'key-1']);
    assert.equal(component.response, '');
    assert.equal(component.pendingRequest, null);
});

test('a version conflict refreshes details without automatically posting again or discarding the draft', async () => {
    let writes = 0;
    const component = setup({
        async post() {
            writes++;
            throw { response: { status: 409, data: { message: 'stale' } } };
        },
        async get() { return { data: { data: { id: 4, version: 2, is_handler: false } } }; },
    });
    await component.respond();
    assert.equal(writes, 1);
    assert.equal(component.detail.version, 2);
    assert.equal(component.detail.is_handler, false);
    assert.equal(component.response, 'Customer response');
    assert.equal(component.busy, false);
});

test('an older list request cannot overwrite a newer search', async () => {
    const pending = [];
    const component = setup({ get: () => new Promise(resolve => pending.push(resolve)) });
    const first = component.loadList(1);
    const second = component.loadList(2);
    pending[1]({ data: { data: [{ id: 2 }], meta: { current_page: 2 } } });
    await second;
    pending[0]({ data: { data: [{ id: 1 }], meta: { current_page: 1 } } });
    await first;
    assert.equal(component.rows[0].id, 2);
    assert.equal(component.meta.current_page, 2);
    assert.equal(component.loading, false);
});

 test('cancelling confirmation does not send a response or internal action', async () => {
    let posts = 0;
    const component = setup({ post: async () => { posts++; } }, async () => false);
    component.note = 'Private note';
    await component.respond();
    await component.addAction('note');
    assert.equal(posts, 0);
    assert.equal(component.response, 'Customer response');
    assert.equal(component.note, 'Private note');
 });

 test('an uncertain transfer retries its original key and preserves the internal reason', async () => {
    const sent = [];
    const component = setup({
        post: async (_url, payload) => { sent.push(payload); throw new Error('Connection lost'); },
    });
    component.transferReason = 'Private escalation';
    component.departmentRole = 9;
    await component.addAction('transfer');
    await component.addAction('transfer');
    assert.equal(sent.length, 2);
    assert.equal(sent[0].idempotency_key, sent[1].idempotency_key);
    assert.equal(component.transferReason, 'Private escalation');
 });

test('custom validation rejects whitespace and missing transfer fields before posting', async () => {
    let posted = 0;
    const component = setup({ post: async () => { posted++; } });
    component.response = '   ';
    await component.respond();
    assert.equal(component.fieldErrors.response, 'Response required');
    await component.addAction('transfer');
    assert.equal(component.fieldErrors.departmentRole, 'Department required');
    assert.equal(component.fieldErrors.transferReason, 'Reason required');
    component.note = 'x'.repeat(3001);
    await component.addAction('note');
    assert.equal(component.fieldErrors.note, 'Too long');
    assert.equal(posted, 0);
});
