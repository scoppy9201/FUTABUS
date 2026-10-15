export function setupAdvancedFilters() {
    const form = document.getElementById('report-filters');
    const panel = document.getElementById('report-advanced-filters');
    const trigger = document.querySelector('[data-report-advanced-open]');
    if (!form || !panel || !trigger) return;

    const fields = [...panel.querySelectorAll('[data-report-advanced-fields] select')];
    let snapshot = [];
    let applied = false;
    const setValue = (field, value) => {
        field.value = value;
        field.dispatchEvent(new Event('change', { bubbles: true }));
    };

    trigger.addEventListener('click', () => {
        snapshot = fields.map((field) => field.value);
        applied = false;
        panel.showModal();
    });
    panel.addEventListener('close', () => {
        if (!applied) fields.forEach((field, index) => setValue(field, snapshot[index]));
    });
    panel.addEventListener('click', (event) => {
        if (event.target === panel) panel.close();
    });
    panel.querySelector('[data-report-advanced-cancel]').addEventListener('click', () => panel.close());
    panel.querySelector('[data-report-advanced-reset]').addEventListener('click', () => {
        fields.forEach((field) => setValue(field, field.querySelector('option[value="all"]') ? 'all' : ''));
    });
    panel.querySelector('[data-report-advanced-apply]').addEventListener('click', () => {
        applied = true;
        panel.close();
        form.requestSubmit(form.querySelector('[data-report-submit]'));
    });
}
