@props([
    'product',
    'layout' => 'grid',
    'titleTag' => 'h2',
    'categoryClass' => 'store-kicker',
])

@php
    $isSearch = $layout === 'search';
    $articleClasses = $isSearch
        ? 'store-search-result-card store-card-interactive'
        : 'store-product-card store-card-interactive';
    $mediaClasses = $isSearch
        ? 'store-media store-search-result-media overflow-hidden'
        : 'store-media store-media-md overflow-hidden';
    $bodyClasses = $isSearch ? 'store-search-result-body' : 'mt-5 flex-1';
    $descriptionClasses = $isSearch ? 'store-text mt-3 line-clamp-2' : 'store-text mt-3 line-clamp-3';
    $actionsClasses = $isSearch ? 'store-search-result-actions' : 'mt-6 flex items-end justify-between gap-4';
    $safeTitleTag = $titleTag === 'h3' ? 'h3' : 'h2';
@endphp

<article {{ $attributes->class([$articleClasses]) }}>
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

    <div class="{{ $mediaClasses }}">
        @if ($product->image_url)
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
        @else
            <span class="text-sm font-semibold uppercase tracking-[0.25em] text-stone-500">Sin imagen</span>
        @endif
    </div>

    <div class="{{ $bodyClasses }}">
        <div>
            <p class="{{ $categoryClass }}">{{ $product->category?->name ?? 'Sin categoria' }}</p>

            @if ($safeTitleTag === 'h3')
                <h3 class="mt-2 store-title-lg">{{ $product->name }}</h3>
            @else
                <h2 class="mt-2 store-title-lg">{{ $product->name }}</h2>
            @endif
        </div>

        <p class="{{ $descriptionClasses }}">{{ $product->description }}</p>
    </div>

    <div class="{{ $actionsClasses }}">
        <div class="shrink-0">
            @if ($product->has_discount)
                <p class="store-price-old">{{ number_format((float) $product->sale_price, 2, ',', '.') }} &euro;</p>
            @endif

            <p class="store-price">{{ number_format((float) $product->discounted_price, 2, ',', '.') }} &euro;</p>
        </div>

        <div class="relative z-20 flex flex-wrap items-center justify-end gap-2">
            @if (auth()->check() && auth()->user()->isAdmin())
                <a
                    href="{{ route('products.show', ['product' => $product, 'edit' => 1]) }}"
                    class="store-button-secondary"
                >
                    Editar
                </a>
            @else
                <x-store.add-to-cart-form
                    :product="$product"
                    button-label="Añadir"
                    button-class="store-button-secondary"
                />
            @endif
        </div>
    </div>

    <a href="{{ route('products.show', $product) }}" class="store-card-overlay-link" aria-label="Ver detalle de {{ $product->name }}">
        <span class="sr-only">Ver detalle de {{ $product->name }}</span>
    </a>
</article>
