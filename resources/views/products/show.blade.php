<x-layouts.store :title="$product->name . ' | Bazar Digital'">
    <section class="store-shell pb-10 pt-10">
        <div class="grid gap-8 lg:grid-cols-[0.95fr_1.05fr]">
            <div class="store-panel">
                <div class="store-media store-media-lg">
                    <span class="text-sm font-semibold uppercase tracking-[0.25em] text-stone-500">Sin imagen</span>
                </div>
            </div>

            <div class="store-panel">
                <p class="store-kicker">{{ $product->category?->name ?? 'Sin categoria' }}</p>
                <h1 class="mt-4 store-title-xl">{{ $product->name }}</h1>
                <p class="store-subtitle-muted">{{ $product->barcode }}</p>

                <div class="store-detail-card">
                    <p class="store-detail-label">Descripcion</p>
                    <div class="store-detail-accent"></div>
                    <p class="store-description mt-5">{{ $product->description }}</p>
                </div>

                @if ($product->qty < 1)
                    <p class="store-alert-stock mt-5">Actualmente sin stock</p>
                @endif

                <div class="mt-8 flex flex-wrap items-end gap-6">
                    <div>
                        @if ($product->has_discount)
                            <p class="text-base text-stone-400 line-through">{{ number_format((float) $product->sale_price, 2, ',', '.') }} &euro;</p>
                        @endif
                        <p class="store-price-lg">{{ number_format((float) $product->discounted_price, 2, ',', '.') }} &euro;</p>
                    </div>
                    @if ($product->has_discount)
                        <span class="store-discount-badge">
                            @if ($product->discount_type === 'percentage')
                                -{{ rtrim(rtrim(number_format((float) $product->discount_value, 2, '.', ''), '0'), '.') }}%
                            @else
                                -{{ number_format((float) $product->discount_value, 2, ',', '.') }} &euro;
                            @endif
                        </span>
                    @endif
                </div>

                <div class="mt-8 flex flex-wrap gap-3">
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
