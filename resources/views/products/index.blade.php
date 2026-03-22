<x-layouts.store title="Productos | Bazar Digital">
    <section class="store-shell pb-16 pt-10">
        <div
            class="store-panel mx-auto"
            style="background-color: #fcfaf7; border-color: #e9e2d8; box-shadow: 0 18px 45px rgba(120, 112, 97, 0.10);"
        >
            <div>
                <p class="store-kicker">Catalogo</p>
                <h1 class="store-heading">Productos del bazar</h1>
                <p class="store-text mt-3 max-w-2xl">
                    @if ($selectedCategory)
                        Mostrando productos de la categoria {{ $selectedCategory->name }}.
                    @else
                        Mostrando todos los productos del catalogo.
                    @endif
                </p>
                @if ($selectedCategory?->description)
                    <p class="store-category-summary">
                        {{ $selectedCategory->description }}
                    </p>
                @endif
            </div>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('products.index', ['sort' => $selectedSort !== 'default' ? $selectedSort : null]) }}" class="{{ $selectedCategory ? 'store-filter-pill' : 'store-filter-pill-active' }}">
                    Todas
                </a>
                @foreach ($categories as $category)
                    <a href="{{ route('products.index', ['category' => $category->url, 'sort' => $selectedSort !== 'default' ? $selectedSort : null]) }}" class="{{ $selectedCategory?->id === $category->id ? 'store-filter-pill-highlight' : 'store-filter-pill' }}">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="store-controls-bar" style="margin-top: 3rem;">
            <form method="GET" action="{{ route('products.index') }}" class="store-controls-inline">
                @if ($selectedCategory)
                    <input type="hidden" name="category" value="{{ $selectedCategory->url }}">
                @endif

                <label for="sort" class="text-sm font-semibold text-stone-500">Ordenar por</label>
                <select
                    id="sort"
                    name="sort"
                    class="rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-stone-700 focus:outline-none focus:ring-2 focus:ring-[#1a5542]"
                    onchange="this.form.submit()"
                >
                    <option value="default" @selected($selectedSort === 'default')>Predeterminado</option>
                    <option value="alphabetical" @selected($selectedSort === 'alphabetical')>Alfabetico A-Z</option>
                    <option value="alphabetical_desc" @selected($selectedSort === 'alphabetical_desc')>Alfabetico Z-A</option>
                    <option value="newest" @selected($selectedSort === 'newest')>Nuevos</option>
                    <option value="price" @selected($selectedSort === 'price')>Precio</option>
                </select>
            </form>
        </div>

        <div
            class="mt-8 grid gap-4 mx-auto"
            style="grid-template-columns: repeat(auto-fit, minmax(16rem, 1fr));"
        >
            @forelse ($products as $product)
                <article class="store-card flex h-full w-full max-w-[18rem] flex-col justify-self-center">
                    <div class="store-media store-media-md">
                        <span class="text-sm font-semibold uppercase tracking-[0.25em] text-stone-500">Sin imagen</span>
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

                        <a href="{{ route('products.show', $product) }}" class="store-button-primary hover:bg-amber-400 hover:text-stone-950">
                            Ver detalle
                        </a>
                    </div>
                </article>
            @empty
                <div class="store-empty">
                    No hay productos para este filtro.
                </div>
            @endforelse
        </div>
    </section>
</x-layouts.store>
