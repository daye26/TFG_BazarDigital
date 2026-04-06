<x-layouts.store title="Tu carrito | Bazar Digital">
    <section class="store-shell pb-16 pt-10">
        <div class="store-toolbar">
            <div>
                <p class="store-kicker">Compra en curso</p>
                <h1 class="store-heading">TU CARRITO</h1>
                <p class="store-text mt-3 max-w-2xl">
                    Ajusta la cantidad de cada producto y revisa el total actual antes del siguiente paso.
                </p>
            </div>

            <div class="store-chip">
                <span data-cart-page-items-count>{{ $itemsCount }}</span> en carrito
            </div>
        </div>

        @if ($hasAvailabilityIssues)
            <div class="app-alert-error mt-6 mb-0">
                Hay productos cuyo stock o disponibilidad ha cambiado. Ajusta esas lineas antes de continuar.
            </div>
        @endif

        <div class="mt-8 grid gap-6 lg:grid-cols-[1.18fr_0.82fr]">
            <div class="space-y-4">
                @forelse ($items as $item)
                    <article
                        class="store-card store-cart-compact-card"
                        data-cart-item-card
                        data-unit-price="{{ number_format((float) $item->unit_price, 2, '.', '') }}"
                        data-base-unit-price="{{ number_format((float) $item->product->sale_price, 2, '.', '') }}"
                    >
                        <div class="store-cart-compact-layout">
                            <div class="min-w-0">
                                <p class="store-cart-barcode">{{ $item->product->barcode }}</p>
                                <h2 class="mt-2">
                                    <a href="{{ route('products.show', $item->product) }}" class="store-title-lg transition hover:text-stone-700 hover:underline">
                                        {{ $item->product->name }}
                                    </a>
                                </h2>

                                <div class="store-cart-price-row">
                                    <p class="store-cart-price" data-cart-item-total>
                                        {{ number_format((float) $item->line_total, 2, ',', '.') }} &euro;
                                    </p>

                                    @if ($item->product->has_discount)
                                        <p class="store-price-old" data-cart-item-base-total>
                                            {{ number_format((float) $item->product->sale_price * $item->quantity, 2, ',', '.') }} &euro;
                                        </p>
                                    @endif
                                </div>

                                @if (! $item->product->is_active)
                                    <p class="store-alert-stock mt-3">No disponible</p>
                                @elseif ($item->product->qty < $item->quantity)
                                    <p class="store-alert-stock mt-3">La cantidad supera el stock actual</p>
                                @endif
                            </div>

                            <div class="store-cart-compact-actions">
                                <label for="quantity-{{ $item->id }}" class="store-detail-label">Cantidad</label>
                                <div class="store-cart-action-row">
                                    <form
                                        method="POST"
                                        action="{{ route('cart.items.update', $item) }}"
                                        class="store-cart-update-card"
                                        data-cart-update-form
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <x-store.quantity-stepper
                                            :id="'quantity-' . $item->id"
                                            name="quantity"
                                            :value="$item->quantity"
                                            :min="1"
                                            :max="max($item->product->qty, 1)"
                                        />
                                    </form>

                                    <form method="POST" action="{{ route('cart.items.destroy', $item) }}">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="store-button-secondary store-cart-delete-button">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="store-empty">
                        <p class="store-title-lg">Tu carrito esta vacio.</p>
                        <p class="store-text mt-3">
                            Todavia no has anadido productos. Puedes volver al catalogo y empezar a preparar la compra.
                        </p>
                        <div class="mt-6">
                            <a href="{{ route('products.index') }}" class="store-button-primary">
                                Ir a la tienda
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>

            <aside class="store-panel h-fit lg:sticky lg:top-6">
                <p class="store-kicker">Resumen</p>
                <h2 class="store-heading">TOTAL ACTUAL</h2>

                <div class="mt-8 space-y-4">
                    <div class="flex items-center justify-between gap-4 text-sm text-stone-600">
                        <span>Articulos en carrito</span>
                        <span class="font-semibold text-stone-900" data-cart-summary-count>{{ $itemsCount }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4 text-sm text-stone-600">
                        <span>Lineas distintas</span>
                        <span class="font-semibold text-stone-900">{{ $items->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4 border-t border-stone-200 pt-4 text-base font-bold text-stone-950">
                        <span>Subtotal</span>
                        <span data-cart-summary-subtotal>{{ number_format((float) $subtotal, 2, ',', '.') }} &euro;</span>
                    </div>
                </div>

                <p class="store-text mt-6">
                    El carrito usa el precio con descuento y el stock actual del producto. Cuando anadas pedidos reales, aqui podras enlazar checkout y reserva de stock.
                </p>

                <div class="mt-8 flex flex-col gap-3">
                    <a href="{{ route('products.index') }}" class="store-button-primary text-center">
                        Seguir comprando
                    </a>

                    @if ($items->isNotEmpty())
                        <form method="POST" action="{{ route('cart.clear') }}">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="store-button-secondary w-full justify-center">
                                Vaciar carrito
                            </button>
                        </form>
                    @endif
                </div>
            </aside>
        </div>
    </section>
</x-layouts.store>
