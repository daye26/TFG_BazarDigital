<x-layouts.store title="Bazar Digital">
    <section class="store-shell pb-10 pt-10 lg:pt-14">
        <div class="grid gap-8 lg:grid-cols-[1.2fr_0.8fr]">
            <div class="store-panel-dark">
                <p class="store-kicker-light">Bazar para el dia a dia</p>
                <h1 class="store-heading-xl max-w-xl text-white">Productos utiles, precios claros y una base limpia para crecer.</h1>
                <p class="mt-6 max-w-2xl text-base leading-7 text-stone-300 sm:text-lg">
                    Esta es la portada inicial del bazar. Ya estamos tirando de base de datos real y mostrando categorias y productos activos.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('products.index') }}" class="store-button-accent">Ver catalogo</a>
                    @guest
                        <a href="{{ route('register') }}" class="store-button-outline-light">Crear cuenta</a>
                    @endguest
                </div>
            </div>

            <div class="store-panel">
                <p class="store-kicker">Categorias</p>
                <div class="mt-6 space-y-4">
                    @forelse ($categories as $category)
                        <a href="{{ route('products.index', ['category' => $category->url]) }}" class="block rounded-2xl border border-stone-200 px-5 py-4 transition hover:border-amber-400 hover:bg-amber-50">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <h2 class="text-lg font-bold text-stone-900">{{ $category->name }}</h2>
                                    <p class="store-text mt-1">{{ $category->description }}</p>
                                </div>
                                <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-stone-500">Explorar</span>
                            </div>
                        </a>
                    @empty
                        <p class="store-text">Todavia no hay categorias.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <section class="store-shell pb-16">
        <div class="mb-6 flex items-end justify-between gap-4">
            <div>
                <p class="store-kicker">Destacados</p>
                <h2 class="store-heading">Productos recientes</h2>
            </div>
            <a href="{{ route('products.index') }}" class="text-sm font-bold text-stone-700 transition hover:text-stone-950">Ver todos</a>
        </div>

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            @forelse ($featuredProducts as $product)
                <article class="store-card store-card-hover group">
                    <div class="store-media store-media-sm">
                        <span class="text-center text-sm font-semibold uppercase tracking-[0.25em] text-stone-500">Sin imagen</span>
                    </div>
                    <div class="mt-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">{{ $product->category?->name ?? 'Sin categoria' }}</p>
                        <h3 class="mt-2 text-xl font-black text-stone-900">{{ $product->name }}</h3>
                        <p class="store-text mt-2 line-clamp-3">{{ $product->description }}</p>
                    </div>
                    <div class="mt-5 flex items-end justify-between gap-3">
                        <div>
                            @if ($product->has_discount)
                                <p class="store-price-old">{{ number_format((float) $product->sale_price, 2, ',', '.') }} &euro;</p>
                            @endif
                            <p class="text-2xl font-black text-stone-950">{{ number_format((float) $product->discounted_price, 2, ',', '.') }} &euro;</p>
                        </div>
                        <a href="{{ route('products.show', $product) }}" class="store-button-primary group-hover:bg-amber-400 group-hover:text-stone-950">
                            Ver
                        </a>
                    </div>
                </article>
            @empty
                <p class="store-text">No hay productos activos todavia.</p>
            @endforelse
        </div>
    </section>
</x-layouts.store>
