import './bootstrap';

const menuButton = document.querySelector('.menu-toggle');
const sidebar = document.querySelector('#sidebar');
menuButton?.addEventListener('click', () => {
    const open = sidebar.classList.toggle('open');
    menuButton.setAttribute('aria-expanded', String(open));
});

document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    });
});

document.querySelectorAll('[data-image-input]').forEach((input) => {
    input.addEventListener('change', () => {
        const file = input.files?.[0];
        const preview = document.getElementById(input.dataset.previewTarget);
        if (file && preview) preview.src = URL.createObjectURL(file);
    });
});

const saleForm = document.querySelector('[data-sale-form]');
if (saleForm) {
    const product = saleForm.querySelector('[data-product-select]');
    const quantity = saleForm.querySelector('[data-quantity]');
    const stockPreview = saleForm.querySelector('[data-stock-preview]');
    const totalPreview = saleForm.querySelector('[data-total-preview]');
    const update = () => {
        const selected = product.options[product.selectedIndex];
        const stock = selected?.dataset.stock;
        const price = Number(selected?.dataset.price || 0);
        const qty = Math.max(0, Number(quantity.value || 0));
        stockPreview.textContent = stock ? `${Number(stock).toLocaleString()} units` : '—';
        totalPreview.textContent = `₱${(price * qty).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    };
    product.addEventListener('change', update);
    quantity.addEventListener('input', update);
    update();
}
