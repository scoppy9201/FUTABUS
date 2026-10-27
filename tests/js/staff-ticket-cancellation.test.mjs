import test from 'node:test';
import assert from 'node:assert/strict';
globalThis.window = { staffTicketList: () => ({}) };
const { staffTicketCancellation } = await import('../../packages/FuteBus/StaffTicketProcessing/src/resources/js/cancellation.js');
function setup(cash = false) {
    window.FutaNotify = { show() {} };
    const state = staffTicketCancellation('/tickets', { context: '/tickets/__TICKET__', store: '/tickets/__TICKET__', csrf: 'csrf', error: 'Error' });
    state.scroll = () => {};
    state.$refs = { reasonForm: { reportValidity: () => true } };
    state.context = { cash, can_refund_cash: true, quote: { refund: 315000 } };
    state.selected = { id: 1 }; state.reason = 'plans'; state.step = 2;
    return state;
}
test('review moves to payment without cancelling or claiming success', () => {
    const state = setup();
    state.proceed();
    assert.equal(state.step, 3);
    assert.equal(state.result, null);
    assert.equal(state.pending, null);
});
test('reason validation is cleared on entry and enabled only when continuing', () => {
    const state = setup();
    const input = { required: true, message: 'Old error', dataset: { requiredMessage: 'Vui lòng chọn lý do hủy vé.' },
        setCustomValidity(message) { this.message = message; }, dispatchEvent() {} };
    state.$refs.cancellationReason = input;
    state.$refs.reasonForm.reportValidity = () => Boolean(state.reason);
    state.reason = '';
    state.resetReasonValidation();
    assert.equal(input.required, false);
    assert.equal(input.message, '');
    state.proceed();
    assert.equal(state.step, 2);
    assert.equal(input.message, input.dataset.requiredMessage);
    state.reason = 'plans';
    state.proceed();
    assert.equal(state.step, 3);
    assert.equal(input.message, '');
    state.back();
    assert.equal(input.required, false);
    assert.equal(input.message, '');
});
test('cash refund cannot submit without explicit confirmation of money returned', async () => {
    const state = setup(true); state.step = 3;
    let requests = 0;
    window.axios = { post: async () => { requests++; return { data: { status: 'refunded' } }; } };
    await state.submitCancellation();
    assert.equal(requests, 0);
    state.cashPaid = true;
    await state.submitCancellation();
    assert.equal(requests, 1);
    assert.equal(state.step, 4);
    assert.equal(state.result.status, 'refunded');
});
test('online refund completes as a pending request, not as money returned', async () => {
    const state = setup(); state.step = 3;
    window.axios = { post: async (url, payload) => {
        assert.equal(payload.cash_paid, false);
        return { data: { status: 'refund_pending', reference: 'ref' } };
    } };
    await state.submitCancellation();
    assert.equal(state.step, 4);
    assert.equal(state.result.status, 'refund_pending');
});
test('online ticket cash selection requires confirmation and sends the selected method', async () => {
    const state = setup(); state.step = 3; state.refundMethod = 'cash';
    let requests = 0;
    window.axios = { post: async (url, payload) => {
        requests++;
        assert.equal(payload.refund_method, 'cash');
        assert.equal(payload.cash_paid, true);
        return { data: { status: 'refunded' } };
    } };
    await state.submitCancellation();
    assert.equal(requests, 0);
    state.cashPaid = true;
    state.context.can_refund_cash = false;
    await state.submitCancellation();
    assert.equal(requests, 0);
    state.context.can_refund_cash = true;
    await state.submitCancellation();
    assert.equal(requests, 1);
    assert.equal(state.result.status, 'refunded');
});
test('lost response retries the same payload and blocks edits and navigation', async () => {
    const state = setup(true); state.step = 3; state.cashPaid = true;
    const payloads = [];
    window.axios = { post: async (url, payload) => {
        payloads.push(structuredClone(payload));
        if (payloads.length === 1) throw new Error('Network lost');
        return { data: { status: 'refunded' } };
    } };
    await state.submitCancellation();
    state.back(); assert.equal(state.step, 3);
    state.note = 'Changed after response lost';
    await state.submitCancellation();
    assert.deepEqual(payloads[0], payloads[1]);
    assert.equal(state.step, 4);
});
test('a changed quote blocks progression until reloaded', async () => {
    const state = setup(); state.step = 3;
    window.axios = { post: async () => { throw { response: { status: 422, data: { errors: { ticket: ['Quote changed'] } } } }; } };
    await state.submitCancellation();
    assert.equal(state.step, 2);
    assert.equal(state.quoteStale, true);
    assert.equal(state.pending, null);
    state.proceed(); assert.equal(state.step, 2);
});
test('repeated clicks cannot submit a second cancellation while busy', async () => {
    const state = setup(); state.step = 3;
    let requests = 0, resolve;
    window.axios = { post: () => { requests++; return new Promise(done => { resolve = done; }); } };
    const first = state.submitCancellation();
    await state.submitCancellation();
    assert.equal(requests, 1);
    resolve({ data: { status: 'refund_pending' } });
    await first;
    assert.equal(state.step, 4);
});
