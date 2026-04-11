<x-layouts.store title="LO + NUEVO | Bazar Digital">
    <section class="store-shell pb-16 pt-10">
        <div class="store-panel store-panel-soft mx-auto">
            <div>
                <p class="store-kicker">Los productos mas recientes</p>
                <h1 class="store-heading">LO + NUEVO</h1>
                <p class="store-text mt-3 max-w-2xl">
                    Los 5 últimos
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
                    <option value="price_asc" @selected($selectedSort === 'price_asc')>Precio: menor a mayor</option>
                    <option value="price_desc" @selected($selectedSort === 'price_desc')>Precio: mayor a menor</option>
                </select>
            </form>
        </div>

        <div class="store-grid-auto mx-auto mt-8">
            @forelse ($products as $product)
                <x-store.responsive-product-card :product="$product" />
            @empty
                <div class="store-empty">
                    No hay productos nuevos todavia.
                </div>
            @endforelse
        </div>
    </section>
</x-layouts.store>
