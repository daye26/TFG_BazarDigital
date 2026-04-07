@props([
    'title',
    'active' => 'dashboard',
    'backHref' => null,
    'backLabel' => 'Volver',
])

@php
    $links = [
        ['key' => 'orders', 'label' => 'Pedidos', 'href' => route('admin.orders.index')],
        ['key' => 'products', 'label' => 'Productos', 'href' => route('admin.products.manage')],
        ['key' => 'categories', 'label' => 'Categorias', 'href' => route('admin.categories.manage')],
        ['key' => 'dashboard', 'label' => 'Panel', 'href' => route('admin.index')],
    ];
@endphp

<div class="app-page-header app-page-header-admin">
    <div class="space-y-3">
        <h2 class="app-page-title">{{ $title }}</h2>

        <nav class="app-admin-nav" aria-label="Secciones del panel de administracion">
            @foreach ($links as $link)
                <a
                    href="{{ $link['href'] }}"
                    class="{{ $active === $link['key'] ? 'app-admin-nav-link-active' : 'app-admin-nav-link' }}"
                >
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>
    </div>

    @if ($backHref)
        <a href="{{ $backHref }}" class="app-button-secondary">
            {{ $backLabel }}
        </a>
    @endif
</div>
