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

                <div class="store-detail-card">
                    <div class="mt-6">
                        <x-store.add-to-cart-form
                            :product="$product"
                            :show-quantity="true"
                            button-label="Añadir al carrito"
                            button-class="store-button-primary"
                            form-class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                            toast-placement="inline"
                            toast-anchor-id="product-cart-toast-anchor"
                        />
                    </div>
                </div>

                <div class="store-product-actions">
                    <a href="{{ route('products.index') }}" class="store-button-primary px-5 py-3">
                        Volver al catalogo
                    </a>
                    <a href="{{ route('products.index', ['category' => $product->category?->url]) }}" class="store-button-secondary px-5 py-3">
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

        <div class="grid gap-5 md:grid-cols-3">
            @forelse ($relatedProducts as $relatedProduct)
                <article class="store-card">
                    <div class="store-media store-media-md overflow-hidden">
                        @if ($relatedProduct->image_url)
                            <img src="{{ $relatedProduct->image_url }}" alt="{{ $relatedProduct->name }}" class="h-full w-full object-cover">
                        @else
                            <span class="text-sm font-semibold uppercase tracking-[0.25em] text-stone-500">Sin imagen</span>
                        @endif
                    </div>
                    <p class="store-kicker">{{ $relatedProduct->category?->name ?? 'Sin categoria' }}</p>
                    <h3 class="mt-2 store-title-lg">{{ $relatedProduct->name }}</h3>
                    <p class="store-text mt-3 line-clamp-3">{{ $relatedProduct->description }}</p>
                    <div class="mt-6 flex items-end justify-between gap-4">
                        <p class="store-price">{{ number_format((float) $relatedProduct->discounted_price, 2, ',', '.') }} &euro;</p>
                        <a href="{{ route('products.show', $relatedProduct) }}" class="store-button-secondary">
                            Abrir
                        </a>
                    </div>
                </article>
            @empty
                <div class="store-empty">
                    No hay productos relacionados todavia.
                </div>
            @endforelse
        </div>
    </section>
</x-layouts.store>
