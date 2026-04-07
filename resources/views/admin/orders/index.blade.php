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
                            @endphp

                            <article class="app-card app-order-summary-card">
                                <details class="app-order-disclosure">
                                    <summary class="app-order-disclosure-summary app-order-summary-head">
                                        <div>
                                            <p class="app-section-kicker">{{ $order->order_number }}</p>
                                            <h4 class="app-order-summary-title">
                                                {{ $order->pickup_name }}
                                            </h4>
                                            <div class="app-order-summary-meta">
                                                <span>{{ $order->created_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}</span>
                                                <span>{{ $order->items->count() }} lineas / {{ $totalUnits }} uds</span>
                                                <span>{{ number_format((float) $order->total, 2, ',', '.') }} &euro; ({{ $order->payment_method->label() }})</span>
                                            </div>
                                        </div>

                                        <div class="app-order-summary-side">
                                            <div class="app-order-summary-status-row">
                                                <span class="store-status-pill {{ match ($order->status->value) { 'ready' => 'store-status-pill-warning', 'completed' => 'store-status-pill-success', 'cancelled' => 'store-status-pill-danger', default => 'store-status-pill-neutral' } }}">
                                                    {{ $order->status->label() }}
                                                </span>

                                                <svg class="app-order-disclosure-chevron h-5 w-5 text-stone-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.94a.75.75 0 011.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                                </svg>
                                            </div>

                                            @if ($order->usesOnlinePayment() && ! $order->isPaid() && $order->status->value !== 'cancelled')
                                                <p class="app-order-summary-note">Esperando pago online</p>
                                            @elseif ($order->isPaid())
                                                <p class="app-order-summary-note">Pago confirmado</p>
                                            @endif
                                        </div>
                                    </summary>

                                    <div class="app-order-disclosure-body">
                                        <x-admin.order-items-panel :order="$order" :show-meta="false" />
                                    </div>
                                </details>

                                <div class="app-order-summary-actions">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="app-button-secondary-compact">
                                            Abrir pedido completo
                                        </a>

                                        @if ($order->status->value === 'pending' && $order->canBePrepared())
                                            <form method="POST" action="{{ route('admin.orders.ready', $order) }}">
                                                @csrf
                                                @method('PATCH')

                                                <button type="submit" class="app-button-primary-compact">
                                                    Marcar como listo
                                                </button>
                                            </form>
                                        @elseif ($order->status->value === 'ready')
                                            <form method="POST" action="{{ route('admin.orders.complete', $order) }}">
                                                @csrf
                                                @method('PATCH')

                                                <button type="submit" class="app-button-primary-compact">
                                                    Marcar como entregado
                                                </button>
                                            </form>
                                        @endif
                                    </div>

                                    @if ($order->status->value === 'pending' && ! $order->canBePrepared())
                                        <p class="app-order-summary-note">
                                            Este pedido online sigue esperando confirmacion de pago antes de entrar en preparacion.
                                        </p>
                                    @endif
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
