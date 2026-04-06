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
                    <a href="{{ route('products.latest') }}" class="store-button-outline-light">LO + NUEVO</a>
                    @guest
                        <a href="{{ route('register') }}" class="store-button-outline-light">Crear cuenta</a>
                    @endguest
                </div>
            </div>

            <div class="store-panel">
                <p class="store-kicker">Categorias</p>
                <div class="mt-6 space-y-4">
                    @forelse ($categories as $category)
                        <a href="{{ route('products.index', ['category' => $category->filter_key]) }}" class="store-category-link">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <h2 class="text-lg font-bold text-stone-900">{{ $category->name }}</h2>
                                    <p class="store-text mt-1">{{ $category->description }}</p>
                                </div>
                                <span class="store-category-link-chip">Explorar</span>
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
                <p class="store-kicker">Ultimos productos creados</p>
                <h2 class="store-heading">LO + NUEVO</h2>
                <p class="store-text mt-3 max-w-2xl">
                    Una vista previa de los ultimos productos que se han incorporado al bazar.
                </p>
            </div>
            <a href="{{ route('products.latest') }}" class="store-button-primary">Entrar</a>
        </div>

        <div class="store-grid-auto">
            @forelse ($latestProductsPreview as $product)
                <x-store.responsive-product-card :product="$product" title-tag="h3" />
            @empty
                <p class="store-text">No hay productos nuevos todavia.</p>
            @endforelse
        </div>
    </section>
</x-layouts.store>
