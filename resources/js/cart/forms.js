import { renderCartToast } from './toast';
import { clampQuantityInputValue, updateQuantityStepperState } from './quantity-stepper';
import {
    extractErrorMessage,
    updateCartCount,
    updateCartItemTotals,
    updateCartSummary,
} from './utils';

const cartButtonTimers = new WeakMap();
const cartUpdateTimers = new WeakMap();
const cartUpdateDebounceMs = 600;

function queueCartUpdate(form, options = {}) {
    const existingTimeout = cartUpdateTimers.get(form);

    if (existingTimeout) {
        window.clearTimeout(existingTimeout);
    }

    const timeout = window.setTimeout(() => {
        cartUpdateTimers.delete(form);
        submitCartUpdateForm(form);
    }, options.immediate ? 0 : cartUpdateDebounceMs);

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
        form.dataset.pendingResubmit = 'true';
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
        updateCartItemTotals(form);
        updateCartCount(payload.cartItemsCount ?? 0);
        updateCartSummary(payload);
    } catch (error) {
        input.value = syncedValue;
        updateQuantityStepperState(input.closest('[data-quantity-stepper]'));
        updateCartItemTotals(form);

        renderCartToast({
            message: error.message,
            tone: 'error',
        });
    } finally {
        form.dataset.submitting = 'false';
        form.removeAttribute('aria-busy');

        const shouldResubmit = form.dataset.pendingResubmit === 'true'
            || input.value !== (form.dataset.syncedQuantity || input.value);

        form.dataset.pendingResubmit = 'false';

        if (shouldResubmit) {
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

export function initAjaxCartForms() {
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

export function initAjaxCartUpdateForms() {
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
        updateCartItemTotals(form);

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            queueCartUpdate(form, { immediate: true });
        });

        input.addEventListener('input', () => {
            updateCartItemTotals(form);
            queueCartUpdate(form);
        });

        input.addEventListener('change', () => {
            updateCartItemTotals(form);
            queueCartUpdate(form, { immediate: true });
        });
    });
}
