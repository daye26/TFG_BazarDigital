<x-layouts.store :title="$product->name . ' | Bazar Digital'">
    <section class="store-shell pb-10 pt-10">
        <div class="grid gap-8 lg:grid-cols-[0.95fr_1.05fr]">
            <div class="store-panel">
                <div class="store-media store-media-lg overflow-hidden">
                    @if ($product->image_url)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                    @else
                        <span class="text-sm font-semibold uppercase tracking-[0.25em] text-stone-500">Sin imagen</span>
                    @endif
                </div>
            </div>

            <div class="store-panel">
                <h1 class="text-4xl font-black tracking-tight text-stone-950 sm:text-5xl">{{ $product->name }}</h1>
                <p class="mt-7 text-2xl font-medium text-stone-500">{{ $product->category?->name ?? 'Sin categoria' }}</p>
                <p class="mt-5 text-lg tracking-[0.03em] text-stone-400">|||| {{ $product->barcode }}</p>

                <div style="margin-top: 2rem;">
                    @if ($product->has_discount)
                        <div class="mb-3 flex flex-wrap items-center gap-4">
                            <p class="text-lg text-stone-400 line-through">{{ number_format((float) $product->sale_price, 2, ',', '.') }} &euro;</p>
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
                        <p class="font-light tracking-tight text-emerald-800" style="font-size: 4rem; line-height: 1;">{{ number_format((float) $product->discounted_price, 2, ',', '.') }} &euro;</p>
                    </div>
                </div>

                <p class="text-xl leading-10 text-slate-500" style="margin-top: 2rem;">{{ $product->description }}</p>

                @if ($product->qty > 0)
                    <p class="mt-8 text-lg font-medium text-stone-700">
                        Disponible
                    </p>
                @else
                    <p class="mt-8 text-lg font-medium text-red-600">
                        Sin stock
                    </p>
                @endif

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
