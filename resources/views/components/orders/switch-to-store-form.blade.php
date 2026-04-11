@props([
    'order',
    'buttonClass' => 'store-button-secondary',
    'label' => 'Cambiar a pago en tienda',
    'redirectTo' => request()->getRequestUri(),
])

<form method="POST" action="{{ route('orders.payment.store', $order) }}">
    @csrf
    @method('PATCH')

    <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">

    <button type="submit" class="{{ $buttonClass }}">
        {{ $label }}
    </button>
</form>
