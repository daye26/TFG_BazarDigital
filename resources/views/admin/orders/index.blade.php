<x-app-layout>
    <x-slot name="header">
        <x-admin.panel-header
            title="Gestion de pedidos"
            active="orders"
            :back-href="route('admin.index')"
            back-label="Volver al panel"
        />
    </x-slot>

    <div class="app-page">
        <div class="app-shell-stack">
            <section class="app-surface">
                <div class="app-hero">
                    <p class="app-hero-kicker">Preparacion de pedidos</p>
                    <h3 class="app-hero-title">Pedidos del bazar</h3>
                    <p class="app-hero-copy">
                        Cada tarjeta permite revisar el contenido del pedido, abrir el detalle completo y avanzar su estado sin salir del panel.
                    </p>
                </div>

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

                    <div class="mb-6 flex flex-wrap gap-3">
                        <a href="{{ route('admin.orders.index') }}" class="{{ $scope === 'all' ? 'app-button-primary' : 'app-button-secondary' }}">Todos</a>
                        <a href="{{ route('admin.orders.index', ['scope' => 'pending']) }}" class="{{ $scope === 'pending' ? 'app-button-primary' : 'app-button-secondary' }}">Pendientes</a>
                        <a href="{{ route('admin.orders.index', ['scope' => 'ready']) }}" class="{{ $scope === 'ready' ? 'app-button-primary' : 'app-button-secondary' }}">Listos</a>
                        <a href="{{ route('admin.orders.index', ['scope' => 'cancelled']) }}" class="{{ $scope === 'cancelled' ? 'app-button-primary' : 'app-button-secondary' }}">Cancelados</a>
                    </div>

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
                                                <a href="{{ route('admin.orders.show', $order) }}" class="app-button-secondary-compact">
                                                    Detalle
                                                </a>

                                                @if ($order->status->value === 'pending' && $order->canBePrepared())
                                                    <form method="POST" action="{{ route('admin.orders.ready', $order) }}">
                                                        @csrf
                                                        @method('PATCH')

                                                        <button type="submit" class="app-button-primary-compact">
                                                            Listo
                                                        </button>
                                                    </form>
                                                @elseif ($order->status->value === 'ready')
                                                    <form method="POST" action="{{ route('admin.orders.complete', $order) }}">
                                                        @csrf
                                                        @method('PATCH')

                                                        <button type="submit" class="app-button-primary-compact">
                                                            Entregado
                                                        </button>
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
                                <p class="text-sm text-stone-500">Todavia no hay pedidos para este filtro.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
