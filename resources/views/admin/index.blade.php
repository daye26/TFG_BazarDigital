<x-app-layout>
    <div class="app-page">
        <div class="app-shell-stack">
            <section class="app-surface">
                <x-admin.page-hero
                    kicker="Zona admin"
                    title="Panel de control"
                />

                <div class="app-surface-body">
                    @if (session('status'))
                        <div class="app-alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <section class="grid gap-5 lg:grid-cols-[minmax(0,1.12fr)_minmax(320px,0.88fr)]">
                        <div class="app-card-muted">
                            <p class="app-section-kicker">Catalogo y mantenimiento</p>
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

                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                            <a href="{{ route('admin.orders.index', ['scope' => 'pending']) }}" class="app-stat-card app-stat-card-link">
                                <p class="app-stat-label">Pedidos pendientes</p>
                                <p class="app-stat-value">{{ $stats['pending_orders'] }}</p>
                            </a>

                            <a href="{{ route('admin.orders.index', ['scope' => 'ready']) }}" class="app-stat-card app-stat-card-link">
                                <p class="app-stat-label">Pedidos listos</p>
                                <p class="app-stat-value-success">{{ $stats['ready_orders'] }}</p>
                            </a>

                            <a href="{{ route('admin.categories.manage', ['scope' => 'active']) }}" class="app-stat-card app-stat-card-link">
                                <p class="app-stat-label">Categorias activas</p>
                                <p class="app-stat-value">{{ $stats['active_categories'] }}</p>
                            </a>

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
                        </div>
                    </section>

                    <section class="mt-8">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <p class="app-section-kicker">Atencion requerida</p>
                                <h4 class="mt-1 text-2xl font-black tracking-tight text-stone-950">Tareas pendientes</h4>
                            </div>
                            <p class="text-sm text-stone-500">Los avisos se ordenan por urgencia y solo aparecen cuando requieren accion.</p>
                        </div>

                        @if ($alerts->isNotEmpty())
                            <div class="mt-6 grid gap-4 xl:grid-cols-2">
                                @foreach ($alerts as $alert)
                                    <article class="{{ $alert['tone'] === 'urgent' ? 'app-attention-card app-attention-card-urgent' : 'app-attention-card app-attention-card-review' }}">
                                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                            <div class="min-w-0">
                                                <span class="store-status-pill {{ $alert['tone'] === 'urgent' ? 'store-status-pill-danger' : 'store-status-pill-warning' }}">
                                                    {{ $alert['tone_label'] }}
                                                </span>
                                                <h5 class="mt-3 text-xl font-black tracking-tight text-stone-950">{{ $alert['title'] }}</h5>
                                                <p class="mt-2 text-sm leading-6 text-stone-600">{{ $alert['summary'] }}</p>
                                            </div>

                                            <div class="shrink-0 rounded-2xl border border-stone-200 bg-white px-4 py-4 shadow-sm">
                                                <p class="app-stat-label">Casos</p>
                                                <p class="mt-3 text-3xl font-black tracking-tight text-stone-950">{{ $alert['count'] }}</p>
                                            </div>
                                        </div>

                                        @if (! empty($alert['items']))
                                            <div class="mt-5 space-y-3">
                                                @foreach ($alert['items'] as $item)
                                                    <a href="{{ $item['href'] }}" class="app-attention-item">
                                                        <div class="min-w-0">
                                                            <p class="truncate text-sm font-bold text-stone-950">{{ $item['title'] }}</p>
                                                            <p class="mt-1 text-xs text-stone-500">{{ $item['subtitle'] }}</p>
                                                        </div>
                                                        <p class="shrink-0 text-xs font-semibold text-stone-600">{{ $item['meta'] }}</p>
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif

                                        <div class="mt-5">
                                            <a href="{{ $alert['action_href'] }}" class="app-button-secondary">
                                                {{ $alert['action_label'] }}
                                            </a>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @else
                            <section class="app-note-card mt-6">
                                <p class="app-note-kicker">Sin incidencias</p>
                                <div class="app-note-copy">
                                    <p>No hay tareas urgentes ni revisiones pendientes en este momento.</p>
                                </div>
                            </section>
                        @endif
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
