import './bootstrap';

const root = document.documentElement;
const updateThemeControls = () => {
    const dark = root.dataset.theme === 'dark';
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.setAttribute('aria-label', dark ? 'Switch to light mode' : 'Switch to dark mode');
    });
};
document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const next = root.dataset.theme === 'dark' ? 'light' : 'dark';
        root.dataset.theme = next;
        localStorage.setItem('cafeflow-theme', next);
        updateThemeControls();
    });
});
updateThemeControls();

if (localStorage.getItem('cafeflow-sidebar') === 'collapsed') document.body.classList.add('sidebar-collapsed');
document.querySelector('[data-sidebar-toggle]')?.addEventListener('click', () => {
    const collapsed = document.body.classList.toggle('sidebar-collapsed');
    localStorage.setItem('cafeflow-sidebar', collapsed ? 'collapsed' : 'expanded');
});

const startLoading = () => document.body.classList.add('is-loading');
window.addEventListener('pageshow', () => document.body.classList.remove('is-loading'));
document.addEventListener('click', (event) => {
    const link = event.target.closest('a[href]');
    if (!link || link.target === '_blank' || link.hasAttribute('download')) return;
    const url = new URL(link.href, window.location.href);
    if (url.origin === window.location.origin && url.href !== window.location.href && !url.hash) startLoading();
});
document.addEventListener('submit', (event) => {
    if (!event.defaultPrevented && event.target.checkValidity()) startLoading();
});

const menuButton = document.querySelector('.menu-toggle');
const sidebar = document.querySelector('#sidebar');
menuButton?.addEventListener('click', () => {
    const open = sidebar.classList.toggle('open');
    menuButton.setAttribute('aria-expanded', String(open));
});

const confirmDialog = document.querySelector('#confirm-dialog');
let pendingForm = null;
document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!confirmDialog) return;
        event.preventDefault();
        pendingForm = form;
        confirmDialog.querySelector('[data-confirm-message]').textContent = form.dataset.confirm;
        confirmDialog.showModal();
    });
});
confirmDialog?.addEventListener('close', () => {
    if (confirmDialog.returnValue === 'confirm' && pendingForm) {
        startLoading();
        pendingForm.submit();
    }
    pendingForm = null;
});

document.querySelectorAll('[data-image-input]').forEach((input) => {
    input.addEventListener('change', () => {
        const file = input.files?.[0];
        const preview = document.getElementById(input.dataset.previewTarget);
        if (file && preview) preview.src = URL.createObjectURL(file);
    });
});

const money = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' });
const saleForm = document.querySelector('[data-sale-form]');
if (saleForm) {
    const options = [...saleForm.querySelectorAll('[data-product-option]')];
    const cards = [...saleForm.querySelectorAll('[data-product-card]')];
    const quantity = saleForm.querySelector('[data-quantity]');
    const customer = saleForm.querySelector('[data-customer-select]');
    const emailReceipt = saleForm.querySelector('[data-email-receipt]');
    const submit = saleForm.querySelector('[data-sale-submit]');
    const selectedEmpty = saleForm.querySelector('[data-selected-empty]');
    const selectedItem = saleForm.querySelector('[data-selected-item]');
    const selectedName = saleForm.querySelector('[data-selected-name]');
    const selectedImage = saleForm.querySelector('[data-selected-image]');
    const unitPrice = saleForm.querySelector('[data-unit-price]');
    const stockPreview = saleForm.querySelector('[data-stock-preview]');
    const totalPreview = saleForm.querySelector('[data-total-preview]');
    const search = saleForm.querySelector('[data-product-search]');
    const emptySearch = saleForm.querySelector('[data-catalog-empty]');
    const resultCount = saleForm.querySelector('[data-result-count]');
    const mobileTotal = saleForm.querySelector('[data-mobile-total]');
    const viewButtons = [...saleForm.querySelectorAll('[data-pos-view]')];

    const updateReceiptOption = () => {
        const enabled = Boolean(customer.value);
        const wasDisabled = emailReceipt.disabled;
        emailReceipt.disabled = !enabled;
        if (!enabled) emailReceipt.checked = false;
        if (enabled && wasDisabled) emailReceipt.checked = true;
    };

    const updateSale = () => {
        const selected = options.find((option) => option.checked);
        cards.forEach((card) => card.classList.toggle('selected', card.contains(selected)));
        if (!selected) {
            selectedEmpty.hidden = false;
            selectedItem.hidden = true;
            stockPreview.textContent = '—';
            totalPreview.textContent = money.format(0);
            submit.disabled = true;
            return;
        }

        const price = Number(selected.dataset.price || 0);
        const qty = Math.max(0, Number(quantity.value || 0));
        const card = selected.closest('[data-product-card]');
        selectedEmpty.hidden = true;
        selectedItem.hidden = false;
        selectedName.textContent = selected.dataset.name;
        selectedImage.src = card.querySelector('img').src;
        unitPrice.textContent = money.format(price);
        stockPreview.textContent = `${Number(selected.dataset.stock).toLocaleString()} units`;
        totalPreview.textContent = money.format(price * qty);
        mobileTotal.textContent = money.format(price * qty);
        submit.disabled = qty < 1;
    };

    const filterProducts = () => {
        const term = search.value.trim().toLocaleLowerCase();
        let visible = 0;
        cards.forEach((card) => {
            const matches = card.dataset.searchValue.includes(term);
            card.hidden = !matches;
            if (matches) visible += 1;
        });
        emptySearch.hidden = visible !== 0;
        resultCount.textContent = `${visible} available`;
    };

    options.forEach((option) => option.addEventListener('change', updateSale));
    quantity.addEventListener('input', updateSale);
    customer.addEventListener('change', updateReceiptOption);
    search.addEventListener('input', filterProducts);
    viewButtons.forEach((button) => button.addEventListener('click', () => {
        const checkout = button.dataset.posView === 'checkout';
        saleForm.classList.toggle('show-checkout', checkout);
        viewButtons.forEach((item) => {
            const active = item === button;
            item.classList.toggle('active', active);
            item.setAttribute('aria-pressed', String(active));
        });
    }));
    saleForm.addEventListener('submit', () => {
        submit.disabled = true;
        submit.textContent = 'Completing sale…';
        submit.setAttribute('aria-busy', 'true');
    });
    updateReceiptOption();
    updateSale();
    if (saleForm.querySelector('.checkout-panel .field-error')) viewButtons.find((button) => button.dataset.posView === 'checkout')?.click();
}

document.querySelector('[data-print]')?.addEventListener('click', () => window.print());
