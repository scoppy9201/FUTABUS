document.querySelectorAll('[data-clear-for]').forEach((button) => {
    const input = document.getElementById(button.dataset.clearFor);
    if (!(input instanceof HTMLInputElement)) return;

    const update = () => { button.hidden = input.value.length === 0; };
    input.addEventListener('input', update);
    button.addEventListener('click', () => {
        input.value = '';
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.focus();
    });
    update();
});
