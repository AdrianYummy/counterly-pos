import './bootstrap';

const root = document.documentElement;
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
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

document.querySelector('[data-sidebar-toggle]')?.addEventListener('click', () => {
    const collapsed = root.classList.toggle('sidebar-collapsed');
    localStorage.setItem('cafeflow-sidebar', collapsed ? 'collapsed' : 'expanded');
});

const startLoading = () => document.body.classList.add('is-loading');
let intentionalNavigation = false;
window.addEventListener('pageshow', () => {
    intentionalNavigation = false;
    document.body.classList.remove('is-loading');
});
document.addEventListener('click', (event) => {
    const link = event.target.closest('a[href]');
    if (!link || link.target === '_blank' || link.hasAttribute('download') || link.hasAttribute('data-bean-transition')) return;
    const url = new URL(link.href, window.location.href);
    if (url.origin === window.location.origin && url.href !== window.location.href && !url.hash) {
        intentionalNavigation = true;
        startLoading();
    }
});
document.addEventListener('submit', (event) => {
    if (!event.defaultPrevented && !event.target.matches('[data-confirm]') && event.target.checkValidity()) {
        intentionalNavigation = true;
        startLoading();
    }
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
        document.body.classList.remove('is-loading');
        pendingForm = form;
        confirmDialog.querySelector('[data-confirm-message]').textContent = form.dataset.confirm;
        confirmDialog.showModal();
    });
});
confirmDialog?.addEventListener('close', () => {
    if (confirmDialog.returnValue === 'confirm' && pendingForm) {
        intentionalNavigation = true;
        startLoading();
        pendingForm.submit();
    }
    if (confirmDialog.returnValue !== 'confirm') document.body.classList.remove('is-loading');
    pendingForm = null;
});

const sessionGuard = document.body.matches('[data-session-guard]') ? document.body : null;
if (sessionGuard) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const heartbeatUrl = sessionGuard.dataset.sessionHeartbeatUrl;
    const activityUrl = sessionGuard.dataset.sessionActivityUrl;
    const closeUrl = sessionGuard.dataset.sessionCloseUrl;
    const loginUrl = sessionGuard.dataset.sessionLoginUrl;
    const idleLimit = Math.max(60, Number(sessionGuard.dataset.sessionIdleSeconds || 900)) * 1000;
    let lastInteraction = Date.now();
    let lastActivitySync = 0;
    let closingSession = false;

    const securePost = async (url) => {
        const response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            keepalive: true,
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        if (response.status === 401 || response.status === 419) {
            window.location.replace(loginUrl);
        }
        return response;
    };

    const closeSession = () => securePost(closeUrl).catch(() => null);
    const lockAndLeave = async () => {
        if (closingSession) return;
        closingSession = true;
        document.documentElement.classList.add('session-restore-lock');
        await Promise.race([
            closeSession(),
            new Promise((resolve) => window.setTimeout(resolve, 1800)),
        ]);
        window.location.replace(loginUrl);
    };

    if (document.documentElement.classList.contains('session-restore-lock')) {
        lockAndLeave();
    } else {
        const recordActivity = () => {
            lastInteraction = Date.now();
            if (lastInteraction - lastActivitySync < 30000) return;
            lastActivitySync = lastInteraction;
            securePost(activityUrl).catch(() => null);
        };
        ['pointerdown', 'keydown', 'touchstart', 'scroll'].forEach((eventName) => {
            window.addEventListener(eventName, recordActivity, { passive: true });
        });
        window.setInterval(() => {
            if (Date.now() - lastInteraction >= idleLimit) {
                lockAndLeave();
                return;
            }
            securePost(heartbeatUrl).catch(() => null);
        }, 45000);
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') securePost(heartbeatUrl).catch(() => null);
        });
    }

    window.addEventListener('pagehide', () => {
        if (intentionalNavigation || closingSession) return;
        closingSession = true;
        const payload = new FormData();
        payload.append('_token', csrfToken);
        if (!navigator.sendBeacon?.(closeUrl, payload)) {
            fetch(closeUrl, {
                method: 'POST',
                body: payload,
                credentials: 'same-origin',
                keepalive: true,
            }).catch(() => null);
        }
    });
}

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
    const cards = [...saleForm.querySelectorAll('[data-product-card]')];
    const customer = saleForm.querySelector('[data-customer-select]');
    const emailReceipt = saleForm.querySelector('[data-email-receipt]');
    const submit = saleForm.querySelector('[data-sale-submit]');
    const cartEmpty = saleForm.querySelector('[data-cart-empty]');
    const cartList = saleForm.querySelector('[data-cart-list]');
    const cartTemplate = saleForm.querySelector('[data-cart-item-template]');
    const cartCount = saleForm.querySelector('[data-cart-count]');
    const productCount = saleForm.querySelector('[data-product-count]');
    const unitCount = saleForm.querySelector('[data-unit-count]');
    const totalPreview = saleForm.querySelector('[data-total-preview]');
    const search = saleForm.querySelector('[data-product-search]');
    const emptySearch = saleForm.querySelector('[data-catalog-empty]');
    const resultCount = saleForm.querySelector('[data-result-count]');
    const mobileCount = saleForm.querySelector('[data-mobile-count]');
    const mobileTotal = saleForm.querySelector('[data-mobile-total]');
    const viewButtons = [...saleForm.querySelectorAll('[data-pos-view]')];
    const cart = new Map();

    const updateReceiptOption = () => {
        const enabled = Boolean(customer.value);
        const wasDisabled = emailReceipt.disabled;
        emailReceipt.disabled = !enabled;
        if (!enabled) emailReceipt.checked = false;
        if (enabled && wasDisabled) emailReceipt.checked = true;
    };

    const updateCart = () => {
        let total = 0;
        let units = 0;
        let invalid = false;

        [...cart.values()].forEach((item, index) => {
            const quantity = Number(item.quantity.value);
            const stock = Number(item.card.dataset.stock);
            const valid = Number.isInteger(quantity) && quantity >= 1 && quantity <= stock;
            item.productInput.name = `items[${index}][product_id]`;
            item.quantity.name = `items[${index}][quantity]`;
            item.quantity.setCustomValidity(quantity > stock ? `Only ${stock} unit(s) are available.` : '');
            item.row.classList.toggle('invalid', !valid);
            item.minus.disabled = quantity <= 1;
            item.plus.disabled = quantity >= stock;
            item.lineTotal.textContent = money.format(item.price * (Number.isFinite(quantity) ? Math.max(quantity, 0) : 0));
            total += valid ? item.price * quantity : 0;
            units += valid ? quantity : 0;
            invalid ||= !valid;
        });

        const count = cart.size;
        cartEmpty.hidden = count > 0;
        cartCount.textContent = count.toLocaleString();
        productCount.textContent = count.toLocaleString();
        unitCount.textContent = units.toLocaleString();
        mobileCount.textContent = count.toLocaleString();
        totalPreview.textContent = money.format(total);
        mobileTotal.textContent = money.format(total);
        submit.disabled = count === 0 || invalid;
    };

    const removeProduct = (productId) => {
        const item = cart.get(productId);
        if (!item) return;
        item.row.remove();
        item.card.classList.remove('selected');
        item.card.setAttribute('aria-pressed', 'false');
        cart.delete(productId);
        updateCart();
    };

    const addProduct = (card, initialQuantity = 1) => {
        const productId = card.dataset.productId;
        if (cart.has(productId)) {
            removeProduct(productId);
            return;
        }

        const row = cartTemplate.content.firstElementChild.cloneNode(true);
        const quantity = row.querySelector('[data-cart-quantity]');
        const productInput = row.querySelector('[data-cart-product-input]');
        const price = Number(card.dataset.price);
        const stock = Number(card.dataset.stock);
        row.querySelector('[data-cart-image]').src = card.dataset.image;
        row.querySelector('[data-cart-name]').textContent = card.dataset.name;
        row.querySelector('[data-cart-unit-price]').textContent = money.format(price);
        row.querySelector('[data-cart-stock]').textContent = stock.toLocaleString();
        row.querySelector('[data-cart-remove]').setAttribute('aria-label', `Remove ${card.dataset.name}`);
        productInput.value = productId;
        quantity.value = initialQuantity;
        quantity.max = stock;

        const item = {
            card,
            row,
            price,
            quantity,
            productInput,
            lineTotal: row.querySelector('[data-cart-line-total]'),
            minus: row.querySelector('[data-quantity-minus]'),
            plus: row.querySelector('[data-quantity-plus]'),
        };
        cart.set(productId, item);
        cartList.append(row);
        card.classList.add('selected');
        card.setAttribute('aria-pressed', 'true');

        row.querySelector('[data-cart-remove]').addEventListener('click', () => removeProduct(productId));
        item.minus.addEventListener('click', () => {
            quantity.value = Math.max(1, Number(quantity.value || 1) - 1);
            updateCart();
        });
        item.plus.addEventListener('click', () => {
            quantity.value = Math.min(stock, Number(quantity.value || 0) + 1);
            updateCart();
        });
        quantity.addEventListener('input', updateCart);
        quantity.addEventListener('change', () => {
            if (!quantity.value) quantity.value = 1;
            updateCart();
        });
        updateCart();
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

    cards.forEach((card) => card.addEventListener('click', () => addProduct(card)));
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
    cards.forEach((card) => {
        const initialQuantity = Number(card.dataset.initialQuantity || 0);
        if (initialQuantity > 0) addProduct(card, initialQuantity);
    });
    updateReceiptOption();
    updateCart();
    if (saleForm.querySelector('.checkout-panel .field-error')) viewButtons.find((button) => button.dataset.posView === 'checkout')?.click();
}

document.querySelector('[data-print]')?.addEventListener('click', () => window.print());

document.querySelectorAll('.landing-body a[href^="#"]').forEach((link) => {
    link.addEventListener('click', (event) => {
        const target = document.getElementById(link.hash.slice(1));
        if (!target) return;
        event.preventDefault();
        target.scrollIntoView({ behavior: reducedMotion.matches ? 'auto' : 'smooth', block: 'start' });
        window.history.replaceState(null, '', link.hash);
    });
});

const beanTransitionLayer = document.querySelector('[data-bean-transition-layer]');
let beanTransitioning = false;
document.querySelectorAll('[data-bean-transition]').forEach((link) => {
    link.addEventListener('click', (event) => {
        if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || beanTransitioning) return;
        if (reducedMotion.matches) {
            startLoading();
            return;
        }

        event.preventDefault();
        beanTransitioning = true;
        const destination = link.href;
        const transitionBean = beanTransitionLayer?.querySelector('.transition-bean');
        document.body.classList.add('bean-transitioning');

        let finished = false;
        const navigate = () => {
            if (finished) return;
            finished = true;
            window.location.assign(destination);
        };
        transitionBean?.addEventListener('animationend', navigate, { once: true });
        window.setTimeout(navigate, 1100);
    });
});
window.addEventListener('pageshow', () => {
    beanTransitioning = false;
    document.body.classList.remove('bean-transitioning');
});

const beanField = document.querySelector('[data-bean-field]');
if (beanField) {
    const total = window.innerWidth < 700 ? 9 : 16;
    let seed = 724225;
    const random = () => {
        seed = (seed * 16807) % 2147483647;
        return (seed - 1) / 2147483646;
    };
    const particles = Array.from({ length: total }, (_, index) => {
        const element = document.createElement('i');
        element.className = 'ambient-bean';
        const size = 15 + random() * 22;
        const particle = {
            element,
            nx: random(),
            ny: random(),
            x: 0,
            y: 0,
            vx: 0,
            vy: 0,
            angle: random() * 360,
            phase: random() * Math.PI * 2,
            depth: 0.55 + random() * 0.75,
            index,
        };
        element.style.setProperty('--bean-size', `${size}px`);
        element.style.setProperty('--bean-opacity', String(0.1 + particle.depth * 0.08));
        beanField.appendChild(element);
        return particle;
    });
    const pointer = { x: 0, y: 0, lastX: 0, lastY: 0, vx: 0, vy: 0, active: false };

    const placeParticles = () => {
        particles.forEach((particle) => {
            if (particle.x === 0 && particle.y === 0) {
                particle.x = particle.nx * window.innerWidth;
                particle.y = particle.ny * window.innerHeight;
            }
            particle.element.style.transform = `translate3d(${particle.x}px, ${particle.y}px, 0) rotate(${particle.angle}deg)`;
        });
    };
    placeParticles();

    window.addEventListener('pointermove', (event) => {
        const wasActive = pointer.active;
        pointer.vx = wasActive ? event.clientX - pointer.lastX : 0;
        pointer.vy = wasActive ? event.clientY - pointer.lastY : 0;
        pointer.lastX = event.clientX;
        pointer.lastY = event.clientY;
        pointer.x = event.clientX;
        pointer.y = event.clientY;
        pointer.active = event.pointerType === 'mouse' || event.pointerType === 'pen';
    }, { passive: true });
    document.documentElement.addEventListener('mouseleave', () => { pointer.active = false; });
    window.addEventListener('resize', placeParticles, { passive: true });

    if (!reducedMotion.matches) {
        const animateBeans = (time) => {
            particles.forEach((particle) => {
                const homeX = particle.nx * window.innerWidth + Math.sin(time * 0.00035 + particle.phase) * 11 * particle.depth;
                const homeY = particle.ny * window.innerHeight + Math.cos(time * 0.00028 + particle.phase) * 8 * particle.depth;
                particle.vx += (homeX - particle.x) * 0.0022;
                particle.vy += (homeY - particle.y) * 0.0022;

                if (pointer.active) {
                    const dx = pointer.x - particle.x;
                    const dy = pointer.y - particle.y;
                    const distance = Math.hypot(dx, dy) || 1;
                    if (distance < 190) {
                        const influence = (1 - distance / 190) * particle.depth;
                        particle.vx -= (dx / distance) * influence * 0.32;
                        particle.vy -= (dy / distance) * influence * 0.32;
                        particle.vx += pointer.vx * influence * 0.032;
                        particle.vy += pointer.vy * influence * 0.032;
                    }
                }

                particle.vx *= 0.945;
                particle.vy *= 0.945;
                particle.x += particle.vx;
                particle.y += particle.vy;
                particle.angle += particle.vx * 0.16 + 0.035 * particle.depth;
                particle.element.style.transform = `translate3d(${particle.x}px, ${particle.y}px, 0) rotate(${particle.angle}deg)`;
            });
            pointer.vx *= 0.82;
            pointer.vy *= 0.82;
            window.requestAnimationFrame(animateBeans);
        };
        window.requestAnimationFrame(animateBeans);
    }
}
