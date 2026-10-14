import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

test('staff account errors appear on blur/input and confirmation updates when the password changes', () => {
    const field = (name, required = false) => {
        const container = { children: [], querySelectorAll: () => [], append(node) { this.children.push(node); } };
        return {
            name, required, value: '', type: 'text', handlers: {}, validity: {}, container,
            closest: () => container, setAttribute() {}, focus() { this.focused = true; },
            addEventListener(event, callback) { this.handlers[event] = callback; },
        };
    };
    const name = field('name', true);
    const phone = field('phone', true);
    const password = field('password');
    const confirmation = field('password_confirmation');
    const form = {
        dataset: { validation: JSON.stringify({
            name_required: 'Name required', phone_required: 'Phone required', phone_invalid: 'Invalid phone',
            password_min: 'Too short', password_confirmation_required: 'Confirmation required', password_confirmed: 'Does not match',
        }) },
        querySelectorAll: () => [name, phone, password, confirmation], handlers: {},
        addEventListener(event, callback) { this.handlers[event] = callback; },
    };
    const document = { readyState: 'complete', querySelectorAll: () => [form], createElement: () => ({ setAttribute() {} }) };
    const source = fs.readFileSync(new URL('../../packages/FuteBus/UserManagement/src/resources/js/staff-form-validation.js', import.meta.url), 'utf8');
    vm.runInNewContext(source, { document });
    name.handlers.blur();
    assert.equal(name.container.children[0].textContent, 'Name required');
    name.value = 'Employee';
    name.handlers.input();
    assert.equal(name.container.children[0].textContent, '');
    phone.value = '123';
    phone.handlers.input();
    assert.equal(phone.container.children[0].textContent, 'Invalid phone');
    confirmation.handlers.blur();
    password.value = 'abcdefgh';
    password.handlers.input();
    assert.equal(confirmation.container.children[0].textContent, 'Confirmation required');
    confirmation.value = 'different';
    confirmation.handlers.input();
    assert.equal(confirmation.container.children[0].textContent, 'Does not match');
    let prevented = false;
    form.handlers.submit({ preventDefault() { prevented = true; } });
    assert.equal(prevented, true);
    assert.equal(phone.focused, true);
});
