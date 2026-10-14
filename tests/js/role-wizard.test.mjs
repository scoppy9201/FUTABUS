import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

function fixture() {
    const node = () => ({ handlers: {}, classList: { toggle() {} },
        addEventListener(type, handler) { this.handlers[type] = handler; }, focus() {} });
    const panels = [1, 2, 3].map(step => ({ dataset: { roleStep: String(step) }, querySelector: () => name }));
    const name = { value: 'Operations', valid: true, checkValidity() { return this.valid; }, reportValidity() {}, focus() {} };
    const description = { value: 'Team role', checkValidity: () => true };
    const checkbox = { checked: true, closest: () => ({ querySelector: () => ({ textContent: '<Admin label>' }) }) };
    const nodes = Object.fromEntries(['next', 'back', 'submit', 'summary-name', 'summary-description', 'count', 'zero', 'permission-search', 'no-results']
        .map(key => [`[data-role-${key}]`, node()]));
    nodes['[data-role-summary-permissions]'] = { replaceChildren(...items) { this.items = items; } };
    const form = { dataset: { initialStep: '1' }, elements: { name, description }, handlers: {},
        querySelector: selector => nodes[selector],
        querySelectorAll: selector => ({
            '[data-role-step]': panels, 'input[name="name"], textarea[name="description"]': [name, description],
            '[data-role-permission]': [checkbox], '[data-role-marker]': [], '[data-role-group-toggle]': [],
        }[selector] || []),
        addEventListener(type, handler) { this.handlers[type] = handler; },
    };
    const context = { document: { readyState: 'loading', addEventListener() {}, createElement: () => ({}) } };
    vm.runInNewContext(fs.readFileSync(new URL('../../packages/FuteBus/RolePermission/src/resources/js/access-modals.js', import.meta.url), 'utf8'), context);
    context.initializeRoleWizard(form);
    return { form, name, checkbox, panels, nodes };
}

test('invalid role details block advancement and Enter advances a step without submitting', () => {
    const { form, name, panels, nodes } = fixture();
    name.valid = false;
    nodes['[data-role-next]'].handlers.click();
    assert.equal(panels[0].hidden, false);
    assert.equal(panels[1].hidden, true);
    name.valid = true;
    let prevented = false;
    form.handlers.submit({ preventDefault() { prevented = true; } });
    assert.equal(prevented, true);
    assert.equal(panels[1].hidden, false);
});

test('review preserves permission choices and renders labels as text', () => {
    const { checkbox, nodes, panels } = fixture();
    nodes['[data-role-next]'].handlers.click();
    nodes['[data-role-next]'].handlers.click();
    assert.equal(panels[2].hidden, false);
    assert.equal(nodes['[data-role-count]'].textContent, 1);
    assert.equal(nodes['[data-role-summary-permissions]'].items[0].textContent, '<Admin label>');
    nodes['[data-role-back]'].handlers.click();
    checkbox.checked = false;
    nodes['[data-role-next]'].handlers.click();
    assert.equal(nodes['[data-role-count]'].textContent, 0);
    assert.equal(nodes['[data-role-zero]'].hidden, false);
});
