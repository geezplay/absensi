import './bootstrap';
import Alpine from 'alpinejs';

Alpine.start();

document.addEventListener('submit', (e) => {
    const form = e.target;
    if (form?.dataset?.confirm) {
        e.preventDefault();
        window.dispatchEvent(new CustomEvent('confirm-ask', {
            detail: { msg: form.dataset.confirm, go: () => form.submit() },
        }));
    }
}, true);

document.addEventListener('click', (e) => {
    const btn = e.target.closest('button[data-confirm]');
    if (!btn) return;
    const form = btn.closest('form');
    if (!form) return;
    e.preventDefault();
    window.dispatchEvent(new CustomEvent('confirm-ask', {
        detail: {
            msg: btn.dataset.confirm,
            go: () => (form.requestSubmit ? form.requestSubmit(btn) : form.submit()),
        },
    }));
}, true);
