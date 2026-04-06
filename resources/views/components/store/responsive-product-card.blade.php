@props([
    'product',
    'titleTag' => 'h2',
    'categoryClass' => 'store-kicker',
])

<div class="sm:hidden">
    <x-store.product-card
        :product="$product"
        layout="search"
        :title-tag="$titleTag"
        :category-class="$categoryClass"
    />
</div>

<div {{ $attributes->class(['hidden h-full sm:block']) }}>
    <x-store.product-card
        :product="$product"
        layout="grid"
        :title-tag="$titleTag"
        :category-class="$categoryClass"
    />
</div>
