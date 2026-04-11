@props([
    'order',
    'buttonClass' => 'store-button-secondary',
    'label' => 'Cambiar a pago en tienda',
    'redirectTo' => request()->getRequestUri(),
])

@php
    $modalName = 'switch-to-store-payment-' . $order->getKey() . '-' . md5($redirectTo);
@endphp

<div class="contents">
    <button
        type="button"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', '{{ $modalName }}')"
        class="{{ $buttonClass }}"
    >
        {{ $label }}
    </button>

    <x-modal :name="$modalName" maxWidth="lg" focusable>
        <form method="POST" action="{{ route('orders.payment.store', $order) }}" class="p-6">
            @csrf
            @method('PATCH')

            <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">

            <h2 class="store-title-lg">
                Cambiar a pago en tienda
            </h2>

            <p class="store-text mt-3">
                El pedido pasara a pago en tienda y quedara pendiente de pago al recogerlo.
            </p>

            <div class="mt-6 flex flex-wrap gap-3">
                <button type="button" class="store-button-secondary" x-on:click="$dispatch('close')">
                    Cancelar
                </button>
                <button type="submit" class="store-button-primary">
                    Confirmar
                </button>
            </div>
        </form>
    </x-modal>
</div>
