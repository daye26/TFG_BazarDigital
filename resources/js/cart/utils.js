import { clampQuantityInputValue } from './quantity-stepper';

const cartPriceFormatter = new Intl.NumberFormat('es-ES', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
});

export function updateCartCount(count) {
    document.querySelectorAll('[data-cart-count]').forEach((node) => {
        node.textContent = count;
    });
}

export function formatCartMoney(amount) {
    return `${cartPriceFormatter.format(amount)} \u20ac`;
}

export function updateCartSummary(payload) {
    if (typeof payload?.cartItemsCount !== 'undefined') {
        document.querySelectorAll('[data-cart-summary-count], [data-cart-page-items-count]').forEach((node) => {
            node.textContent = payload.cartItemsCount;
        });
    }

    if (payload?.cartSubtotal) {
        document.querySelectorAll('[data-cart-summary-subtotal]').forEach((node) => {
            node.textContent = `${payload.cartSubtotal} \u20ac`;
        });
    }
}

export function updateCartItemTotals(form) {
    const card = form.closest('[data-cart-item-card]');
    const input = form.querySelector('[data-stepper-input]');

    if (!card || !input) {
        return;
    }

    const quantity = clampQuantityInputValue(input);
    const unitPrice = Number.parseFloat(card.dataset.unitPrice || '0');
    const baseUnitPrice = Number.parseFloat(card.dataset.baseUnitPrice || '0');
    const totalNode = card.querySelector('[data-cart-item-total]');
    const baseTotalNode = card.querySelector('[data-cart-item-base-total]');

    if (totalNode) {
        totalNode.textContent = formatCartMoney(unitPrice * quantity);
    }

    if (baseTotalNode) {
        baseTotalNode.textContent = formatCartMoney(baseUnitPrice * quantity);
    }
}

export function extractErrorMessage(payload) {
    if (payload?.errors) {
        const firstError = Object.values(payload.errors)[0];

        if (Array.isArray(firstError) && firstError[0]) {
            return firstError[0];
        }
    }

    return payload?.message || 'No se ha podido actualizar el carrito.';
}
