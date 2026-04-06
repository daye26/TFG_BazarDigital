export function renderCartToast({ message, placement = 'floating', anchorId = '', tone = 'success' }) {
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

export function initCartToastFromSession() {
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
