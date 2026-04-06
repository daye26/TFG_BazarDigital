export function updateQuantityStepperState(stepper) {
    const input = stepper?.querySelector('[data-stepper-input]');
    const decrementButton = stepper?.querySelector('[data-stepper-decrement]');
    const incrementButton = stepper?.querySelector('[data-stepper-increment]');

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

export function clampQuantityInputValue(input) {
    const min = Number.parseInt(input.min || '1', 10);
    const max = input.max ? Number.parseInt(input.max, 10) : Number.POSITIVE_INFINITY;
    const rawValue = Number.parseInt(input.value || `${min}`, 10);
    const nextValue = Number.isNaN(rawValue)
        ? min
        : Math.min(Math.max(rawValue, min), max);

    input.value = `${nextValue}`;

    return nextValue;
}

export function initQuantitySteppers() {
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
