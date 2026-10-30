import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const notificationSource = fs.readFileSync(new URL('../../packages/FuteBus/Core/src/resources/js/notification-center.js', import.meta.url), 'utf8');

function setup(openDialog = false) {
    const timers = [];
    const host = { children: [], append(node) { this.children.push(node); } };
    const dialog = { append(node) { node.parentElement = this; } };
    const document = {
        documentElement: { lang: 'vi' },
        body: { append(node) { node.parentElement = this; } },
        querySelectorAll: () => openDialog ? [dialog] : [],
        getElementById: () => host,
        createElement: () => ({
            leaving: false,
            style: { setProperty() {} },
            classList: { add() { this.owner.leaving = true; } },
            setAttribute() {}, addEventListener() {}, append() {},
            remove() { host.children.splice(host.children.indexOf(this), 1); },
        }),
    };
    const createElement = document.createElement;
    document.createElement = () => {
        const node = createElement();
        node.classList.owner = node;
        return node;
    };
    const window = { location: { assign(url) { this.url = url; } } };
    const context = vm.createContext({
        window, document,
        setTimeout(callback, delay) { timers.push({ callback, delay }); return timers.length; },
        clearTimeout() {},
    });
    vm.runInContext(notificationSource, context);
    return { context, host, timers, window, dialog, document };
}

test('system notifications are displayed within the active modal and return to the page after it closes', () => {
    const { window, host, dialog, document } = setup(true);
    window.FutaNotify.error('Request already assigned');
    assert.equal(host.parentElement, dialog);
    document.querySelectorAll = () => [];
    window.FutaNotify.success('Saved');
    assert.equal(host.parentElement, document.body);
});

for (const view of ['StaffDashboard/src/resources/views/staff.blade.php', 'StaffTicketProcessing/src/resources/views/tickets.blade.php']) {
    const template = fs.readFileSync(new URL('../../packages/FuteBus/' + view, import.meta.url), 'utf8');
    const expression = template.match(/@click="(groups\?\.tickets\.sale[^"\n]+)"/)[1]
        .replace(/@js\((?:route|__)\('[^']+'\)\)/g, '"test-value"');

    test(`${view}: denied sale stays visible for four seconds without Alpine invoking its dismiss callback`, () => {
        const { context, host, timers } = setup();
        context.groups = { tickets: { sale: false } };
        const result = vm.runInContext(expression, context);
        // Alpine evaluates function-valued expression results automatically.
        if (typeof result === 'function') result();
        assert.equal(host.children.length, 1);
        assert.equal(host.children[0].leaving, false);
        assert.equal(timers.length, 1);
        assert.equal(timers[0].delay, 4000);
        timers[0].callback();
        assert.equal(host.children[0].leaving, true);
        assert.equal(timers[1].delay, 180);
        timers[1].callback();
        assert.equal(host.children.length, 0);
    });

    test(`${view}: permitted sale navigates without displaying a denial`, () => {
        const { context, host, window } = setup();
        context.groups = { tickets: { sale: true } };
        vm.runInContext(expression, context);
        assert.equal(window.location.url, 'test-value');
        assert.equal(host.children.length, 0);
    });
}
