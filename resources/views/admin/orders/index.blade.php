<x-app-layout>
    <div class="app-page">
        <div class="app-shell-stack">
            <section class="app-surface">
                <x-admin.page-hero
                    kicker="Preparacion de pedidos"
                    title="Pedidos del bazar"
                    description="Cada tarjeta permite revisar el contenido del pedido, abrir el detalle completo y avanzar su estado sin salir del panel."
                />

                <div class="app-surface-body">
                    @if (session('status'))
                        <div class="app-alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="app-alert-error">
                            {{ $errors->first('order') ?: $errors->first() }}
                        </div>
                    @endif

                    <div class="mb-6 space-y-4">
                        <form method="GET" action="{{ route('admin.orders.index') }}" class="flex flex-col gap-3 lg:flex-row lg:flex-wrap lg:items-center">
                            @if ($scope !== 'all')
                                <input type="hidden" name="scope" value="{{ $scope }}">
                            @endif

                            <label for="admin-order-search" class="sr-only">Buscar por codigo de pedido</label>
                            <input
                                id="admin-order-search"
                                name="q"
                                type="search"
                                value="{{ $searchQuery }}"
                                class="app-filter-input app-filter-input-search lg:w-auto lg:min-w-[22rem]"
                                placeholder="Codigo de pedido"
                            >

                            <label for="admin-order-date" class="sr-only">Filtrar pedidos por fecha</label>
                            <input
                                id="admin-order-date"
                                name="date"
                                type="date"
                                value="{{ $selectedDate ?? '' }}"
                                class="app-filter-input lg:w-auto"
                            >

                            <div class="flex shrink-0 items-center gap-3">
                                <button type="submit" class="app-button-secondary">
                                    Filtrar
                                </button>

                                <a href="{{ route('admin.orders.index', array_filter(['scope' => $scope !== 'all' ? $scope : null])) }}" class="app-button-secondary">
                                    Limpiar
                                </a>
                            </div>
                        </form>

                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('admin.orders.index', array_filter(['q' => $searchQuery !== '' ? $searchQuery : null, 'date' => $selectedDate])) }}" class="{{ $scope === 'all' ? 'app-button-primary' : 'app-button-secondary' }}">Todos</a>
                            <a href="{{ route('admin.orders.index', array_filter(['scope' => 'pending', 'q' => $searchQuery !== '' ? $searchQuery : null, 'date' => $selectedDate])) }}" class="{{ $scope === 'pending' ? 'app-button-primary' : 'app-button-secondary' }}">Pendientes</a>
                            <a href="{{ route('admin.orders.index', array_filter(['scope' => 'preparable', 'q' => $searchQuery !== '' ? $searchQuery : null, 'date' => $selectedDate])) }}" class="{{ $scope === 'preparable' ? 'app-button-primary' : 'app-button-secondary' }}">Por preparar</a>
                            <a href="{{ route('admin.orders.index', array_filter(['scope' => 'ready', 'q' => $searchQuery !== '' ? $searchQuery : null, 'date' => $selectedDate])) }}" class="{{ $scope === 'ready' ? 'app-button-primary' : 'app-button-secondary' }}">Listos</a>
                            <a href="{{ route('admin.orders.index', array_filter(['scope' => 'cancelled', 'q' => $searchQuery !== '' ? $searchQuery : null, 'date' => $selectedDate])) }}" class="{{ $scope === 'cancelled' ? 'app-button-primary' : 'app-button-secondary' }}">Cancelados</a>
                            <a href="{{ route('admin.orders.index', array_filter(['scope' => 'overdue_preparable', 'q' => $searchQuery !== '' ? $searchQuery : null, 'date' => $selectedDate])) }}" class="{{ $scope === 'overdue_preparable' ? 'app-button-primary' : 'app-button-secondary' }}">Preparar +24h</a>
                            <a href="{{ route('admin.orders.index', array_filter(['scope' => 'stale_ready', 'q' => $searchQuery !== '' ? $searchQuery : null, 'date' => $selectedDate])) }}" class="{{ $scope === 'stale_ready' ? 'app-button-primary' : 'app-button-secondary' }}">Listos +48h</a>
                        </div>
                    </div>

                    @php
                        $currentPage = $orders->currentPage();
                    @endphp

                    <div class="space-y-3">
                        @forelse ($orders as $order)
                            @php
                                $totalUnits = (int) $order->items->sum('quantity');
                                $isCancelled = $order->status->value === 'cancelled';
                                $cancelReason = trim((string) $order->cancel_reason);
                            @endphp

                            <article class="app-card app-order-summary-card" x-data="{ open: false }">
                                <div
                                    class="app-order-summary-head"
                                    role="button"
                                    tabindex="0"
                                    @click="if (! $event.target.closest('[data-no-toggle]')) open = ! open"
                                    @keydown.enter.prevent="open = ! open"
                                    @keydown.space.prevent="open = ! open"
                                    :aria-expanded="open.toString()"
                                    aria-controls="order-preview-{{ $order->id }}"
                                >
                                    <div class="app-order-summary-main">
                                        <div class="app-order-summary-order-row">
                                            <p class="app-section-kicker">{{ $order->order_number }}</p>
                                            <span class="store-status-pill {{ match ($order->status->value) { 'ready' => 'store-status-pill-warning', 'completed' => 'store-status-pill-success', 'cancelled' => 'store-status-pill-danger', default => 'store-status-pill-neutral' } }}">
                                                {{ $order->status->label() }}
                                            </span>
                                        </div>

                                        <div class="app-order-summary-title-row">
                                            <h4 class="app-order-summary-title">
                                                {{ $order->pickup_name }}
                                            </h4>
                                            <div class="app-order-summary-meta">
                                                @if ($isCancelled)
                                                    <span>Creado {{ $order->created_at?->format('d/m/Y H:i') ?? 'sin fecha' }}</span>
                                                    <span>Cancelado {{ $order->updated_at?->format('d/m/Y H:i') ?? 'sin fecha' }}</span>
                                                    <span title="{{ $cancelReason !== '' ? $cancelReason : 'Sin motivo indicado' }}">
                                                        Motivo: {{ \Illuminate\Support\Str::limit($cancelReason !== '' ? $cancelReason : 'Sin motivo indicado', 90) }}
                                                    </span>
                                                @else
                                                    <span>{{ $order->created_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}</span>
                                                    <span>{{ $order->items->count() }} lineas / {{ $totalUnits }} uds</span>
                                                    <span>{{ number_format((float) $order->total, 2, ',', '.') }} &euro; ({{ $order->payment_method->label() }})</span>
                                                @endif
                                            </div>
                                        </div>

                                        @if ($order->usesOnlinePayment() && ! $order->isPaid() && $order->status->value !== 'cancelled')
                                            <p class="mt-2 app-order-summary-note">Esperando pago online</p>
                                        @endif
                                    </div>

                                    <div class="app-order-summary-side">
                                        <div class="app-order-summary-actions-row">
                                            <div class="app-order-summary-controls" data-no-toggle>
                                                <a href="{{ route('admin.orders.documents.download', ['order' => $order, 'format' => 'ticket']) }}" class="app-button-secondary-compact">
                                                    Ticket
                                                </a>

                                                <a href="{{ route('admin.orders.show', array_filter([
                                                    'order' => $order->id,
                                                    'scope' => $scope !== 'all' ? $scope : null,
                                                    'q' => $searchQuery !== '' ? $searchQuery : null,
                                                    'date' => $selectedDate,
                                                    'page' => $currentPage > 1 ? $currentPage : null,
                                                ])) }}" class="app-button-secondary-compact">
                                                    Detalle
                                                </a>

                                                @if ($order->status->value === 'pending' && $order->canBePrepared())
                                                    <form method="POST" action="{{ route('admin.orders.ready', $order) }}" x-data="{ confirming: false }" class="flex items-center gap-2">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="return_context" value="index">
                                                        <input type="hidden" name="return_scope" value="{{ $scope }}">
                                                        <input type="hidden" name="return_q" value="{{ $searchQuery }}">
                                                        <input type="hidden" name="return_date" value="{{ $selectedDate ?? '' }}">
                                                        <input type="hidden" name="return_page" value="{{ $currentPage }}">

                                                        <button type="button" class="app-button-primary-compact" x-show="!confirming" @click="confirming = true">
                                                            Listo
                                                        </button>

                                                        <div x-cloak x-show="confirming" class="flex items-center gap-2">
                                                            <button type="submit" class="app-button-success-compact">
                                                                Confirmar
                                                            </button>
                                                            <button type="button" class="app-button-danger-compact" @click="confirming = false">
                                                                Cancelar
                                                            </button>
                                                        </div>
                                                    </form>
                                                @elseif ($order->status->value === 'ready')
                                                    <form method="POST" action="{{ route('admin.orders.complete', $order) }}" x-data="{ confirming: false }" class="flex items-center gap-2">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="return_context" value="index">
                                                        <input type="hidden" name="return_scope" value="{{ $scope }}">
                                                        <input type="hidden" name="return_q" value="{{ $searchQuery }}">
                                                        <input type="hidden" name="return_date" value="{{ $selectedDate ?? '' }}">
                                                        <input type="hidden" name="return_page" value="{{ $currentPage }}">

                                                        <button type="button" class="app-button-primary-compact" x-show="!confirming" @click="confirming = true">
                                                            Entregado
                                                        </button>

                                                        <div x-cloak x-show="confirming" class="flex items-center gap-2">
                                                            <button type="submit" class="app-button-success-compact">
                                                                Confirmar
                                                            </button>
                                                            <button type="button" class="app-button-danger-compact" @click="confirming = false">
                                                                Cancelar
                                                            </button>
                                                        </div>
                                                    </form>
                                                @endif
                                            </div>

                                            <button
                                                type="button"
                                                class="app-order-disclosure-toggle"
                                                @click.stop="open = ! open"
                                                :aria-expanded="open.toString()"
                                                aria-controls="order-preview-{{ $order->id }}"
                                                data-no-toggle
                                            >
                                                <span class="sr-only">Mostrar contenido del pedido {{ $order->order_number }}</span>
                                                <svg class="app-order-disclosure-chevron h-5 w-5" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.94a.75.75 0 011.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div id="order-preview-{{ $order->id }}" x-cloak x-show="open" class="app-order-disclosure-body">
                                    <x-admin.order-items-panel :order="$order" :show-meta="false" />
                                </div>
                            </article>
                        @empty
                            <div class="app-card">
                                <p class="text-sm text-stone-500">
                                    {{ $searchQuery !== '' || $selectedDate ? 'No hay pedidos para los filtros seleccionados.' : 'Todavia no hay pedidos para este filtro.' }}
                                </p>
                            </div>
                        @endforelse
                    </div>

                    @if ($orders->hasPages())
                        <div class="mt-8">
                            {{ $orders->links() }}
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
