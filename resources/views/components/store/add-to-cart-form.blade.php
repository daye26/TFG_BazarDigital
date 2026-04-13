@props([
    'product',
    'showQuantity' => false,
    'buttonLabel' => 'Añadir al carrito',
    'buttonClass' => 'store-button-primary',
    'formClass' => 'inline-flex',
    'toastPlacement' => 'floating',
    'toastAnchorId' => null,
])

@if ($product->qty < 1)
    <span class="store-alert-stock">Sin stock</span>
@elseif (auth()->check() && ! auth()->user()->isAdmin())
    @php($quantityFieldId = 'cart-quantity-' . $product->id)
    <form
        method="POST"
        action="{{ route('cart.items.store') }}"
        class="{{ $formClass }}"
        data-cart-form
        data-toast-placement="{{ $toastPlacement }}"
        data-toast-anchor-id="{{ $toastAnchorId }}"
    >
        @csrf

        <input type="hidden" name="product_id" value="{{ $product->id }}">

        @if ($showQuantity)
            <div class="flex flex-wrap items-center gap-3">
                <label for="{{ $quantityFieldId }}" class="store-kicker">Cantidad</label>
                <x-store.quantity-stepper
                    :id="$quantityFieldId"
                    name="quantity"
                    :value="1"
                    :min="1"
                    :max="$product->qty"
                />
                <span class="text-xs font-medium text-stone-500">
                    Stock disponible: {{ $product->qty }}
                </span>
            </div>
        @else
            <input type="hidden" name="quantity" value="1">
        @endif

        <button
            type="submit"
            class="{{ $buttonClass }}"
            data-cart-submit
            data-default-label="{{ $buttonLabel }}"
            data-loading-label="Añadiendo..."
            data-success-label="Añadido"
        >
            {{ $buttonLabel }}
        </button>
    </form>
@elseif (auth()->check())
    <span class="store-text">La compra solo esta disponible para clientes.</span>
@else
    <a href="{{ route('login') }}" class="{{ $buttonClass }}">
        Inicia sesión para comprar
    </a>
@endif
