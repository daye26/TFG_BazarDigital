import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const cartButtonTimers = new WeakMap();
const cartUpdateTimers = new WeakMap();

function renderCartToast({ message, placement = 'floating', anchorId = '', tone = 'success' }) {
    if (!message) {
        return;
    }

    const enterFromClasses = placement === 'inline'
        ? ['opacity-0', 'translate-y-2']
        : ['opacity-0', '-translate-y-3'];
    const leaveToClasses = placement === 'inline'
        ? ['opacity-0', '-translate-y-2']
        : ['opacity-0', '-translate-y-4'];
    const toneClasses = tone === 'error'
        ? {
            base: 'app-alert-error',
            shadow: placement === 'inline' ? 'shadow-md shadow-rose-200/50' : 'shadow-lg shadow-rose-200/60',
        }
        : {
            base: 'app-alert-success',
            shadow: placement === 'inline' ? 'shadow-md shadow-emerald-200/50' : 'shadow-lg shadow-emerald-200/60',
        };

    const toast = document.createElement('div');
    toast.textContent = message;
    toast.className = placement === 'inline'
        ? `${toneClasses.base} mb-0 ${toneClasses.shadow} transition-all duration-500 opacity-0 translate-y-2`
        : `${toneClasses.base} pointer-events-auto mb-0 ${toneClasses.shadow} transition-all duration-500 opacity-0 -translate-y-3`;

    let container;
    let usesPersistentAnchor = false;

    if (placement === 'inline') {
        const anchor = anchorId ? document.getElementById(anchorId) : null;

        if (anchor) {
            usesPersistentAnchor = true;
            container = anchor;
            container.className = 'store-shell pb-6';
            container.replaceChildren(toast);
        } else {
            container = document.createElement('section');
            container.className = 'store-shell pb-6';
            container.appendChild(toast);
            document.querySelector('main')?.prepend(container);
        }
    } else {
        container = document.createElement('div');
        container.className = 'pointer-events-none fixed inset-x-0 top-6 z-[70] px-4 sm:px-6 lg:px-8';

        const inner = document.createElement('div');
        inner.className = 'mx-auto max-w-5xl';
        inner.appendChild(toast);
        container.appendChild(inner);
        document.body.appendChild(container);
    }

    window.requestAnimationFrame(() => {
        toast.classList.remove(...enterFromClasses);
        toast.classList.add('opacity-100', 'translate-y-0');
    });

    window.setTimeout(() => {
        toast.classList.remove('opacity-100', 'translate-y-0');
        toast.classList.add(...leaveToClasses);

        window.setTimeout(() => {
            if (usesPersistentAnchor) {
                container.replaceChildren();
                container.className = '';
            } else {
                container.remove();
            }
        }, 520);
    }, 2600);
}

function initCartToastFromSession() {
    const source = document.querySelector('[data-cart-toast-source]');

    if (!source) {
        return;
    }

    renderCartToast({
        message: source.textContent?.trim(),
        placement: source.dataset.placement === 'inline' ? 'inline' : 'floating',
        anchorId: source.dataset.anchorId ?? '',
    });

    source.remove();
}

function updateCartCount(count) {
    document.querySelectorAll('[data-cart-count]').forEach((node) => {
        node.textContent = count;
    });
}

function updateQuantityStepperState(stepper) {
    const input = stepper.querySelector('[data-stepper-input]');
    const decrementButton = stepper.querySelector('[data-stepper-decrement]');
    const incrementButton = stepper.querySelector('[data-stepper-increment]');

    if (!input || !decrementButton || !incrementButton) {
        return;
    }

    const rawValue = Number.parseInt(input.value || input.min || '1', 10);
    const min = Number.parseInt(input.min || '1', 10);
    const max = input.max ? Number.parseInt(input.max, 10) : Number.POSITIVE_INFINITY;
    const value = Number.isNaN(rawValue) ? min : rawValue;

    decrementButton.disabled = value <= min;
    incrementButton.disabled = value >= max;
}

function clampQuantityInputValue(input) {
    const min = Number.parseInt(input.min || '1', 10);
    const max = input.max ? Number.parseInt(input.max, 10) : Number.POSITIVE_INFINITY;
    const rawValue = Number.parseInt(input.value || `${min}`, 10);
    const nextValue = Number.isNaN(rawValue)
        ? min
        : Math.min(Math.max(rawValue, min), max);

    input.value = `${nextValue}`;

    return nextValue;
}

function initQuantitySteppers() {
    document.querySelectorAll('[data-quantity-stepper]').forEach((stepper) => {
        if (stepper.dataset.initialized === 'true') {
            return;
        }

        const input = stepper.querySelector('[data-stepper-input]');
        const decrementButton = stepper.querySelector('[data-stepper-decrement]');
        const incrementButton = stepper.querySelector('[data-stepper-increment]');

        if (!input || !decrementButton || !incrementButton) {
            return;
        }

        stepper.dataset.initialized = 'true';

        const changeValue = (delta) => {
            const currentValue = clampQuantityInputValue(input);
            const min = Number.parseInt(input.min || '1', 10);
            const max = input.max ? Number.parseInt(input.max, 10) : Number.POSITIVE_INFINITY;
            const nextValue = Math.min(Math.max(currentValue + delta, min), max);

            input.value = `${nextValue}`;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
            updateQuantityStepperState(stepper);
        };

        decrementButton.addEventListener('click', () => {
            changeValue(-1);
        });

        incrementButton.addEventListener('click', () => {
            changeValue(1);
        });

        input.addEventListener('input', () => {
            updateQuantityStepperState(stepper);
        });

        input.addEventListener('blur', () => {
            clampQuantityInputValue(input);
            updateQuantityStepperState(stepper);
        });

        clampQuantityInputValue(input);
        updateQuantityStepperState(stepper);
    });
}

function updateCartSummary(payload) {
    if (typeof payload?.cartItemsCount !== 'undefined') {
        document.querySelectorAll('[data-cart-summary-count], [data-cart-page-items-count]').forEach((node) => {
            node.textContent = payload.cartItemsCount;
        });
    }

    if (payload?.cartSubtotal) {
        document.querySelectorAll('[data-cart-summary-subtotal]').forEach((node) => {
            node.textContent = `${payload.cartSubtotal} €`;
        });
    }
}

function queueCartUpdate(form, options = {}) {
    const existingTimeout = cartUpdateTimers.get(form);

    if (existingTimeout) {
        window.clearTimeout(existingTimeout);
    }

    const timeout = window.setTimeout(() => {
        cartUpdateTimers.delete(form);
        submitCartUpdateForm(form);
    }, options.immediate ? 0 : 320);

    cartUpdateTimers.set(form, timeout);
}

async function submitCartUpdateForm(form) {
    const input = form.querySelector('[data-stepper-input]');

    if (!input) {
        return;
    }

    clampQuantityInputValue(input);

    const currentValue = input.value;
    const syncedValue = form.dataset.syncedQuantity || currentValue;

    if (form.dataset.submitting === 'true') {
        return;
    }

    if (currentValue === syncedValue) {
        return;
    }

    form.dataset.submitting = 'true';
    form.setAttribute('aria-busy', 'true');

    try {
        const response = await window.fetch(form.action, {
            method: form.method || 'POST',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
            body: new window.FormData(form),
            credentials: 'same-origin',
        });

        const payload = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(extractErrorMessage(payload));
        }

        form.dataset.syncedQuantity = `${payload.itemQuantity ?? currentValue}`;
        input.value = form.dataset.syncedQuantity;
        updateQuantityStepperState(input.closest('[data-quantity-stepper]'));
        updateCartCount(payload.cartItemsCount ?? 0);
        updateCartSummary(payload);
    } catch (error) {
        input.value = syncedValue;
        updateQuantityStepperState(input.closest('[data-quantity-stepper]'));

        renderCartToast({
            message: error.message,
            tone: 'error',
        });
    } finally {
        form.dataset.submitting = 'false';
        form.removeAttribute('aria-busy');

        if (input.value !== (form.dataset.syncedQuantity || input.value)) {
            queueCartUpdate(form, { immediate: true });
        }
    }
}

function setCartButtonState(submitButton, state) {
    if (!submitButton) {
        return;
    }

    const existingTimeout = cartButtonTimers.get(submitButton);

    if (existingTimeout) {
        window.clearTimeout(existingTimeout);
        cartButtonTimers.delete(submitButton);
    }

    const defaultLabel = submitButton.dataset.defaultLabel || submitButton.textContent;
    const loadingLabel = submitButton.dataset.loadingLabel || 'A\u00f1adiendo...';
    const successLabel = submitButton.dataset.successLabel || 'A\u00f1adido';

    if (state === 'loading') {
        submitButton.disabled = true;
        submitButton.textContent = loadingLabel;

        return;
    }

    if (state === 'success') {
        submitButton.disabled = false;
        submitButton.textContent = successLabel;

        const restoreTimeout = window.setTimeout(() => {
            submitButton.textContent = defaultLabel;
            cartButtonTimers.delete(submitButton);
        }, 2200);

        cartButtonTimers.set(submitButton, restoreTimeout);

        return;
    }

    submitButton.disabled = false;
    submitButton.textContent = defaultLabel;
}

function extractErrorMessage(payload) {
    if (payload?.errors) {
        const firstError = Object.values(payload.errors)[0];

        if (Array.isArray(firstError) && firstError[0]) {
            return firstError[0];
        }
    }

    return payload?.message || 'No se ha podido actualizar el carrito.';
}

function initAjaxCartForms() {
    document.querySelectorAll('[data-cart-form]').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const submitButton = form.querySelector('[data-cart-submit]');
            setCartButtonState(submitButton, 'loading');

            try {
                const response = await window.fetch(form.action, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: new window.FormData(form),
                    credentials: 'same-origin',
                });

                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(extractErrorMessage(payload));
                }

                updateCartCount(payload.cartItemsCount ?? 0);
                setCartButtonState(submitButton, 'success');

                renderCartToast({
                    message: payload.message,
                    placement: form.dataset.toastPlacement === 'inline' ? 'inline' : 'floating',
                    anchorId: form.dataset.toastAnchorId ?? '',
                });
            } catch (error) {
                setCartButtonState(submitButton, 'default');

                renderCartToast({
                    message: error.message,
                    placement: form.dataset.toastPlacement === 'inline' ? 'inline' : 'floating',
                    anchorId: form.dataset.toastAnchorId ?? '',
                    tone: 'error',
                });
            }
        });
    });
}

function initAjaxCartUpdateForms() {
    document.querySelectorAll('[data-cart-update-form]').forEach((form) => {
        if (form.dataset.initialized === 'true') {
            return;
        }

        const input = form.querySelector('[data-stepper-input]');

        if (!input) {
            return;
        }

        form.dataset.initialized = 'true';
        form.dataset.syncedQuantity = `${clampQuantityInputValue(input)}`;
        updateQuantityStepperState(input.closest('[data-quantity-stepper]'));

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            queueCartUpdate(form, { immediate: true });
        });

        input.addEventListener('input', () => {
            queueCartUpdate(form);
        });

        input.addEventListener('change', () => {
            queueCartUpdate(form, { immediate: true });
        });
    });
}

initCartToastFromSession();
initQuantitySteppers();
initAjaxCartForms();
initAjaxCartUpdateForms();
