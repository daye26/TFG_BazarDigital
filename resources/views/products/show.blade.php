<x-layouts.store :title="$product->name . ' | Bazar Digital'">
    <section class="store-shell pb-10 pt-10">
        <div class="store-product-layout">
            <div class="store-panel store-product-panel">
                <div class="store-media store-media-product overflow-hidden">
                    @if ($product->image_url)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                    @else
                        <span class="text-sm font-semibold uppercase tracking-[0.25em] text-stone-500">Sin imagen</span>
                    @endif
                </div>
            </div>

            <div class="store-panel store-product-panel">
                <h1 class="store-product-title">{{ $product->name }}</h1>
                <p class="store-product-category">{{ $product->category?->name ?? 'Sin categoria' }}</p>
                <p class="store-product-barcode">|||| {{ $product->barcode }}</p>

                <div class="store-product-price-block">
                    @if ($product->has_discount)
                        <div class="mb-2 flex flex-wrap items-center gap-3">
                            <p class="text-base text-stone-400 line-through sm:text-lg">{{ number_format((float) $product->sale_price, 2, ',', '.') }} &euro;</p>
                            <span class="store-discount-badge">
                                @if ($product->discount_type === 'percentage')
                                    -{{ rtrim(rtrim(number_format((float) $product->discount_value, 2, '.', ''), '0'), '.') }}%
                                @else
                                    -{{ number_format((float) $product->discount_value, 2, ',', '.') }} &euro;
                                @endif
                            </span>
                        </div>
                    @endif

                    <div>
                        <p class="store-price-hero">{{ number_format((float) $product->discounted_price, 2, ',', '.') }} &euro;</p>
                    </div>
                </div>

                <p class="store-description-emphasis">{{ $product->description }}</p>

                @if (auth()->check() && auth()->user()->isAdmin())
                    @include('products.partials.admin-inline-editor')
                @else
                    <div class="store-detail-card store-product-purchase-card">
                        <div>
                            <x-store.add-to-cart-form
                                :product="$product"
                                :show-quantity="true"
                                button-class="store-button-primary"
                                form-class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                                toast-placement="inline"
                                toast-anchor-id="product-cart-toast-anchor"
                            />
                        </div>
                    </div>

                    @if ($switchableOrders->isNotEmpty())
                        <div class="store-detail-card mt-6 space-y-4">
                            <div>
                                <p class="store-detail-label">Pedido pendiente</p>
                                <h2 class="store-title-lg">Este producto ya esta en un pedido con pago online</h2>
                                <p class="store-text mt-3">
                                    Si todavia no has pagado ese pedido, puedes cambiarlo ahora a pago en tienda. Despues no se podra volver a pago online.
                                </p>
                            </div>

                            <div class="space-y-3">
                                @foreach ($switchableOrders as $switchableOrder)
                                    <div class="rounded-[1.5rem] border border-stone-200 bg-white p-4 shadow-sm shadow-stone-200/60">
                                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                            <div>
                                                <p class="store-kicker text-stone-700">{{ $switchableOrder->order_number }}</p>
                                                <p class="store-text mt-2">
                                                    {{ $switchableOrder->items_count }} lineas | {{ number_format((float) $switchableOrder->total, 2, ',', '.') }} &euro;
                                                </p>
                                            </div>

                                            <div class="flex flex-wrap gap-3">
                                                <a href="{{ route('orders.show', $switchableOrder) }}" class="store-button-secondary">
                                                    Ver pedido
                                                </a>

                                                <x-orders.switch-to-store-form
                                                    :order="$switchableOrder"
                                                    button-class="store-button-primary"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif

                <div class="store-product-actions">
                    <a href="{{ route('products.index') }}" class="store-button-primary px-5 py-3">
                        Volver al catalogo
                    </a>
                    <a href="{{ route('products.index', ['category' => $product->category?->filter_key]) }}" class="store-button-secondary px-5 py-3">
                        Ver categoria
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div id="product-cart-toast-anchor"></div>

    <section class="store-shell pb-16">
        <div class="mb-6 flex items-end justify-between gap-4">
            <div>
                <p class="store-kicker">Mas productos</p>
                <h2 class="store-heading">Relacionados</h2>
            </div>
        </div>

        <div class="store-grid-auto">
            @forelse ($relatedProducts as $relatedProduct)
                <x-store.responsive-product-card :product="$relatedProduct" title-tag="h3" />
            @empty
                <div class="store-empty">
                    No hay productos relacionados todavia.
                </div>
            @endforelse
        </div>
    </section>

    @if (auth()->check() && auth()->user()->isAdmin())
        @include('admin.products.partials.pricing-form-script')
    @endif
</x-layouts.store>
