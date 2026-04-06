<x-app-layout>
    <x-slot name="header">
        <h2 class="app-page-title">
            Panel de control
        </h2>
    </x-slot>

    <div class="app-page">
        <div class="app-shell-stack">
            <section class="app-surface">
                <div class="app-hero">
                    <p class="app-hero-kicker">Zona admin</p>
                    <h3 class="app-hero-title">Panel de control</h3>
                    <p class="app-hero-copy">
                        Desde aqui puedes entrar al alta de productos y categorias cuando haga falta y seguir ampliando el panel con mas utilidades de gestion.
                    </p>
                </div>

                <div class="app-surface-body">
                    @if (session('status'))
                        <div class="app-alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <section class="grid gap-5 lg:grid-cols-[minmax(0,1.35fr)_minmax(320px,0.85fr)]">
                        <div class="app-card-muted">
                            <p class="app-section-kicker">Panel admin</p>
                            <h4 class="app-section-title">Acciones rapidas</h4>

                            <div class="mt-6 flex flex-wrap gap-3">
                                <a href="{{ route('admin.products.create') }}" class="app-button-primary">
                                    Nuevo producto
                                </a>

                                <a href="{{ route('admin.products.manage') }}" class="app-button-primary">
                                    Actualizar producto
                                </a>

                                <a href="{{ route('admin.categories.create') }}" class="app-button-primary">
                                    Nueva categoria
                                </a>

                                <a href="{{ route('admin.categories.manage') }}" class="app-button-primary">
                                    Actualizar categoria
                                </a>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <a href="{{ route('admin.products.manage') }}" class="app-stat-card app-stat-card-link">
                                <p class="app-stat-label">Productos totales</p>
                                <p class="app-stat-value">{{ $stats['total_products'] }}</p>
                            </a>

                            <a href="{{ route('admin.products.manage', ['scope' => 'active']) }}" class="app-stat-card app-stat-card-link">
                                <p class="app-stat-label">Productos activos</p>
                                <p class="app-stat-value-success">{{ $stats['active_products'] }}</p>
                            </a>

                            <a href="{{ route('admin.products.manage', ['scope' => 'inactive']) }}" class="app-stat-card app-stat-card-link">
                                <p class="app-stat-label">Productos ocultos</p>
                                <p class="app-stat-value">{{ $stats['inactive_products'] }}</p>
                            </a>

                            <a href="{{ route('admin.categories.manage', ['scope' => 'active']) }}" class="app-stat-card app-stat-card-link">
                                <p class="app-stat-label">Categorias activas</p>
                                <p class="app-stat-value">{{ $stats['active_categories'] }}</p>
                            </a>
                        </div>
                    </section>

                    <section class="app-card mt-8">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <p class="app-section-kicker">Actividad reciente</p>
                                <h4 class="mt-1 text-2xl font-black tracking-tight text-stone-950">Ultimos productos</h4>
                            </div>
                            <a href="{{ route('admin.products.create') }}" class="app-button-secondary">
                                Crear producto
                            </a>
                        </div>

                        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            @forelse ($latestProducts as $product)
                                <article class="app-mini-card">
                                    <p class="app-mini-card-title">{{ $product->name }}</p>
                                    <p class="app-mini-card-meta">{{ $product->barcode }}</p>
                                    <div class="app-mini-card-row">
                                        <span class="text-stone-500">{{ $product->category?->name ?? 'Sin categoria' }}</span>
                                        <span class="font-bold text-stone-950">{{ number_format((float) $product->sale_price, 2, ',', '.') }} &euro;</span>
                                    </div>
                                    <div class="mt-4">
                                        <a href="{{ route('admin.products.manage', ['product' => $product->id]) }}" class="app-button-secondary">
                                            Editar producto
                                        </a>
                                    </div>
                                </article>
                            @empty
                                <p class="text-sm text-stone-500">Todavia no hay productos creados.</p>
                            @endforelse
                        </div>
                    </section>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
