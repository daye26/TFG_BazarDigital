<x-layouts.store title="LO + NUEVO | Bazar Digital">
    <section class="store-shell pb-16 pt-10">
        <div class="store-panel store-panel-soft mx-auto">
            <div>
                <p class="store-kicker">Los productos mas recientes</p>
                <h1 class="store-heading">LO + NUEVO</h1>
                <p class="store-text mt-3 max-w-2xl">
                    Aqui se muestran los 20 ultimos productos creados.
                </p>
            </div>
        </div>

        <div class="store-controls-bar-spaced">
            <form method="GET" action="{{ route('products.latest') }}" class="store-controls-inline">
                <label for="sort" class="store-sort-label">Ordenar por</label>
                <select
                    id="sort"
                    name="sort"
                    class="store-sort-select"
                    onchange="this.form.submit()"
                >
                    <option value="default" @selected($selectedSort === 'default')>Predeterminado</option>
                    <option value="alphabetical" @selected($selectedSort === 'alphabetical')>Alfabetico A-Z</option>
                    <option value="alphabetical_desc" @selected($selectedSort === 'alphabetical_desc')>Alfabetico Z-A</option>
                    <option value="price" @selected($selectedSort === 'price')>Precio</option>
                </select>
            </form>
        </div>

        <div class="store-grid-auto mx-auto mt-8">
            @forelse ($products as $product)
                <article class="store-product-card">
                    @if ($product->has_discount)
                        <div class="store-discount-stack">
                            <span class="store-discount-pill">
                                @if ($product->discount_type === 'percentage')
                                    -{{ rtrim(rtrim(number_format((float) $product->discount_value, 2, '.', ''), '0'), '.') }}%
                                @else
                                    -{{ number_format((float) $product->discount_value, 2, ',', '.') }} &euro;
                                @endif
                            </span>
                        </div>
                    @endif

                    <div class="store-media store-media-md overflow-hidden">
                        @if ($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                        @else
                            <span class="text-sm font-semibold uppercase tracking-[0.25em] text-stone-500">Sin imagen</span>
                        @endif
                    </div>

                    <div class="mt-5 flex-1">
                        <div>
                            <p class="store-kicker">{{ $product->category?->name ?? 'Sin categoria' }}</p>
                            <h2 class="mt-2 store-title-lg">{{ $product->name }}</h2>
                        </div>

                        <p class="store-text mt-3 line-clamp-3">{{ $product->description }}</p>
                        @if ($product->qty < 1)
                            <p class="store-alert-stock mt-3">Sin stock</p>
                        @endif
                    </div>

                    <div class="mt-6 flex items-end justify-between gap-4">
                        <div>
                            @if ($product->has_discount)
                                <p class="store-price-old">{{ number_format((float) $product->sale_price, 2, ',', '.') }} &euro;</p>
                            @endif
                            <p class="store-price">{{ number_format((float) $product->discounted_price, 2, ',', '.') }} &euro;</p>
                        </div>

                        <a href="{{ route('products.show', $product) }}" class="store-button-primary-highlight">
                            Ver detalle
                        </a>
                    </div>
                </article>
            @empty
                <div class="store-empty">
                    No hay productos nuevos todavia.
                </div>
            @endforelse
        </div>
    </section>
</x-layouts.store>
