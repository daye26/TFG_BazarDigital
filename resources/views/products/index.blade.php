<x-layouts.store title="Productos | Bazar Digital">
    <section class="store-shell pb-16 pt-10">
        <div class="store-panel store-panel-soft mx-auto">
            <div>
                <p class="store-kicker">Productos del bazar</p>
                <h1 class="store-heading">LA TIENDA</h1>
                <p class="store-text mt-3 max-w-2xl">
                    @if ($searchQuery !== '' && $selectedCategory)
                        Resultados para "{{ $searchQuery }}" dentro de la categoria {{ $selectedCategory->name }}.
                    @elseif ($searchQuery !== '')
                        Resultados para "{{ $searchQuery }}" en todo el catalogo.
                    @elseif ($selectedCategory)
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

            <div class="mt-8">
                <p class="store-kicker">Filtros</p>
                <div class="mt-4 flex flex-wrap gap-3">
                    <a href="{{ route('products.index', ['q' => $searchQuery !== '' ? $searchQuery : null, 'sort' => $selectedSort !== 'default' ? $selectedSort : null]) }}" class="{{ $selectedCategory ? 'store-filter-pill' : 'store-filter-pill-active' }}">
                        Todas
                    </a>
                    @foreach ($categories as $category)
                        <a href="{{ route('products.index', ['category' => $category->filter_key, 'q' => $searchQuery !== '' ? $searchQuery : null, 'sort' => $selectedSort !== 'default' ? $selectedSort : null]) }}" class="{{ $selectedCategory?->id === $category->id ? 'store-filter-pill-highlight' : 'store-filter-pill' }}">
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        @if ($searchQuery !== '' && $relatedCategories->isNotEmpty())
            <div class="store-panel mx-auto mt-6">
                <p class="store-kicker">Categorias relacionadas</p>
                <p class="store-text mt-3 max-w-2xl">
                    Si eliges una categoria, entraras en la tienda para ver todos los productos de esa categoria.
                </p>
                <div class="store-search-related">
                    @foreach ($relatedCategories as $category)
                        <a
                            href="{{ route('products.index', ['category' => $category->filter_key, 'sort' => $selectedSort !== 'default' ? $selectedSort : null]) }}"
                            class="store-filter-pill"
                            aria-label="Ver categoria {{ $category->name }}"
                        >
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="store-controls-bar-spaced">
            <form method="GET" action="{{ route('products.index') }}" class="store-controls-inline">
                @if ($selectedCategory)
                    <input type="hidden" name="category" value="{{ $selectedCategory->filter_key }}">
                @endif

                @if ($searchQuery !== '')
                    <input type="hidden" name="q" value="{{ $searchQuery }}">
                @endif

                <label for="sort" class="store-sort-label">Ordenar por</label>
                <select
                    id="sort"
                    name="sort"
                    class="store-sort-select"
                    onchange="this.form.submit()"
                >
                    <option value="default" @selected($selectedSort === 'default')>{{ $searchQuery !== '' ? 'Relevancia' : 'Predeterminado' }}</option>
                    <option value="alphabetical" @selected($selectedSort === 'alphabetical')>Alfabetico A-Z</option>
                    <option value="alphabetical_desc" @selected($selectedSort === 'alphabetical_desc')>Alfabetico Z-A</option>
                    <option value="newest" @selected($selectedSort === 'newest')>Nuevos</option>
                    <option value="price" @selected($selectedSort === 'price')>Precio</option>
                </select>
            </form>
        </div>

        <div class="{{ $searchQuery !== '' ? 'store-search-results-list mx-auto mt-8' : 'store-grid-auto mx-auto mt-8' }}">
            @forelse ($products as $product)
                @if ($searchQuery !== '')
                    <x-store.product-card
                        :product="$product"
                        layout="search"
                        category-class="store-kicker store-kicker-muted"
                    />
                @else
                    <x-store.responsive-product-card
                        :product="$product"
                        category-class="store-kicker store-kicker-muted"
                    />
                @endif
            @empty
                <div class="store-empty">
                    @if ($searchQuery !== '')
                        No hay productos que coincidan con tu busqueda.
                    @else
                        No hay productos para este filtro.
                    @endif
                </div>
            @endforelse
        </div>
    </section>
</x-layouts.store>
