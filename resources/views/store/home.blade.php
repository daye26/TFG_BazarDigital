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
                <p class="store-kicker">Ultimos productos creados</p>
                <h2 class="store-heading">LO + NUEVO</h2>
                <p class="store-text mt-3 max-w-2xl">
                    Una vista previa de los ultimos productos que se han incorporado al bazar.
                </p>
            </div>
            <a href="{{ route('products.latest') }}" class="store-button-primary">Entrar</a>
        </div>

        <div
            class="grid gap-4"
            style="grid-template-columns: repeat(auto-fit, minmax(16rem, 1fr));"
        >
            @forelse ($latestProductsPreview as $product)
                <article class="store-card relative flex h-full w-full max-w-[18rem] flex-col justify-self-center">
                    @if ($product->has_discount)
                        <div class="absolute left-3 top-3 z-10 flex flex-col gap-2">
                            <span class="rounded-full bg-red-600 px-3 py-1 text-xs font-semibold text-white">
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
                            <h3 class="mt-2 store-title-lg">{{ $product->name }}</h3>
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
                <p class="store-text">No hay productos nuevos todavia.</p>
            @endforelse
        </div>
    </section>
</x-layouts.store>
