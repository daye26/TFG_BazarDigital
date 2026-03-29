<x-layouts.store title="Producto no encontrado | Bazar Digital">
    <section class="store-shell pb-10 pt-10">
        <div class="store-panel mx-auto max-w-4xl">
            <p class="store-kicker">Producto no disponible</p>
            <h1 class="store-heading">Ese producto no existe o ya no esta disponible</h1>
            <p class="store-text mt-4 max-w-2xl">
                Puede que el enlace sea antiguo, que el producto se haya eliminado o que ya no este activo en el catalogo.
                Te dejamos accesos rapidos para seguir navegando sin salir de la tienda.
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('products.index') }}" class="store-button-primary px-5 py-3">
                    Ver catalogo
                </a>
                <a href="{{ route('products.latest') }}" class="store-button-secondary px-5 py-3">
                    Ver novedades
                </a>
                <a href="{{ route('home') }}" class="store-button-secondary px-5 py-3">
                    Volver al inicio
                </a>
            </div>
        </div>
    </section>

    <section class="store-shell pb-16">
        <div class="mb-6 flex items-end justify-between gap-4">
            <div>
                <p class="store-kicker">Mientras tanto</p>
                <h2 class="store-heading">Productos recientes</h2>
            </div>
        </div>

        <div class="grid gap-5 md:grid-cols-3">
            @forelse ($suggestedProducts as $product)
                <article class="store-card">
                    <p class="store-kicker">{{ $product->category?->name ?? 'Sin categoria' }}</p>
                    <h3 class="mt-2 store-title-lg">{{ $product->name }}</h3>
                    <p class="store-text mt-3 line-clamp-3">{{ $product->description }}</p>
                    <div class="mt-6 flex items-end justify-between gap-4">
                        <p class="store-price">{{ number_format((float) $product->discounted_price, 2, ',', '.') }} &euro;</p>
                        <a href="{{ route('products.show', $product) }}" class="store-button-secondary">
                            Abrir
                        </a>
                    </div>
                </article>
            @empty
                <div class="store-empty">
                    No hay productos sugeridos por ahora.
                </div>
            @endforelse
        </div>
    </section>
</x-layouts.store>
