@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl'
])

@php
$maxWidth = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
][$maxWidth];
@endphp

<div
    x-data="{
        show: @js($show),
        leaveDuration: 200,
        closeTimer: null,
        bodyStateApplied: false,
        focusables() {
            // All focusable element types...
            let selector = 'a, button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])'
            return [...$el.querySelectorAll(selector)]
                // All non-disabled elements...
                .filter(el => ! el.hasAttribute('disabled'))
        },
        firstFocusable() { return this.focusables()[0] },
        lastFocusable() { return this.focusables().slice(-1)[0] },
        nextFocusable() { return this.focusables()[this.nextFocusableIndex()] || this.firstFocusable() },
        prevFocusable() { return this.focusables()[this.prevFocusableIndex()] || this.lastFocusable() },
        nextFocusableIndex() { return (this.focusables().indexOf(document.activeElement) + 1) % (this.focusables().length + 1) },
        prevFocusableIndex() { return Math.max(0, this.focusables().indexOf(document.activeElement)) -1 },
        applyBodyState() {
            if (this.closeTimer) {
                clearTimeout(this.closeTimer);
                this.closeTimer = null;
            }

            if (this.bodyStateApplied) {
                return;
            }

            const openCount = Number(document.body.dataset.modalOpenCount || 0);

            document.body.dataset.modalOpenCount = String(openCount + 1);
            document.body.classList.add('overflow-y-hidden', 'store-modal-active');

            this.bodyStateApplied = true;
        },
        scheduleBodyRelease() {
            if (! this.bodyStateApplied) {
                return;
            }

            if (this.closeTimer) {
                clearTimeout(this.closeTimer);
            }

            this.closeTimer = setTimeout(() => {
                if (! this.show) {
                    this.releaseBodyState();
                }
            }, this.leaveDuration);
        },
        releaseBodyState() {
            if (! this.bodyStateApplied) {
                return;
            }

            const openCount = Math.max(0, Number(document.body.dataset.modalOpenCount || 0) - 1);

            if (openCount === 0) {
                document.body.classList.remove('overflow-y-hidden', 'store-modal-active');
                delete document.body.dataset.modalOpenCount;
            } else {
                document.body.dataset.modalOpenCount = String(openCount);
            }

            this.bodyStateApplied = false;
            this.closeTimer = null;
        },
    }"
    x-init="if (show) {
        applyBodyState();
    }

    $watch('show', value => {
        if (value) {
            applyBodyState();
            {{ $attributes->has('focusable') ? 'setTimeout(() => firstFocusable().focus(), 100)' : '' }}
        } else {
            scheduleBodyRelease();
        }
    })"
    x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
    x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false"
    x-on:keydown.tab.prevent="$event.shiftKey || nextFocusable().focus()"
    x-on:keydown.shift.tab.prevent="prevFocusable().focus()"
    x-cloak
    x-show="show"
    class="app-modal-shell"
>
    <div
        x-show="show"
        class="app-modal-backdrop"
        x-on:click="show = false"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="app-modal-overlay"></div>
    </div>

    <div
        x-show="show"
        class="app-modal-dialog {{ $maxWidth }}"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    >
        {{ $slot }}
    </div>
</div>
