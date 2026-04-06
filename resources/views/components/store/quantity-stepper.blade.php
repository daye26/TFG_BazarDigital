@props([
    'id',
    'name' => 'quantity',
    'value' => 1,
    'min' => 1,
    'max' => null,
    'decrementLabel' => 'Reducir cantidad',
    'incrementLabel' => 'Aumentar cantidad',
])

<div class="store-quantity-stepper" data-quantity-stepper>
    <button
        type="button"
        class="store-quantity-stepper-button"
        data-stepper-decrement
        aria-label="{{ $decrementLabel }}"
    >
        -
    </button>

    <input
        id="{{ $id }}"
        type="number"
        name="{{ $name }}"
        value="{{ $value }}"
        min="{{ $min }}"
        @if ($max !== null) max="{{ $max }}" @endif
        class="store-quantity-stepper-input"
        data-stepper-input
    >

    <button
        type="button"
        class="store-quantity-stepper-button"
        data-stepper-increment
        aria-label="{{ $incrementLabel }}"
    >
        +
    </button>
</div>
