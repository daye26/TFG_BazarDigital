<x-app-layout>
    <x-slot name="header">
        <div class="app-page-header">
            <h2 class="app-page-title">
                Gestion de pedidos
            </h2>
            <a href="{{ route('admin.index') }}" class="app-button-secondary">
                Volver al panel
            </a>
        </div>
    </x-slot>

    <div class="app-page">
        <div class="app-shell-stack">
            <section class="app-surface">
                <div class="app-hero">
                    <p class="app-hero-kicker">Preparacion de pedidos</p>
                    <h3 class="app-hero-title">Pedidos del bazar</h3>
                    <p class="app-hero-copy">
                        Los pedidos en tienda pueden prepararse desde que se crean. Los pedidos online aparecen bloqueados hasta que Stripe confirme el pago.
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

                    <div class="space-y-4">
                        @forelse ($orders as $order)
                            <article class="app-card">
                                <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                    <div>
                                        <p class="app-section-kicker">{{ $order->order_number }}</p>
                                        <h4 class="mt-2 text-2xl font-black tracking-tight text-stone-950">
                                            {{ $order->pickup_name }}
                                        </h4>
                                        <p class="mt-3 text-sm text-stone-600">
                                            {{ $order->user?->name ?? 'Sin usuario' }} · {{ $order->items->count() }} lineas · {{ number_format((float) $order->total, 2, ',', '.') }} &euro;
                                        </p>
                                    </div>

                                    <div class="flex flex-wrap gap-2">
                                        <span class="store-status-pill {{ match ($order->status->value) { 'ready' => 'store-status-pill-warning', 'completed' => 'store-status-pill-success', 'cancelled' => 'store-status-pill-danger', default => 'store-status-pill-neutral' } }}">
                                            {{ strtoupper($order->status->value) }}
                                        </span>
                                        <span class="store-status-pill {{ $order->isPaid() ? 'store-status-pill-success' : 'store-status-pill-neutral' }}">
                                            PAGO {{ strtoupper($order->payment_status->value) }}
                                        </span>
                                        <span class="store-status-pill store-status-pill-neutral">
                                            {{ strtoupper($order->payment_method->value) }}
                                        </span>
                                    </div>
                                </div>

                                @if ($order->status->value === 'cancelled' && $order->cancel_reason)
                                    <p class="mt-4 text-sm text-rose-700">
                                        Motivo de cancelacion: {{ $order->cancel_reason }}
                                    </p>
                                @endif

                                @if ($order->status->value === 'pending' && $order->canBePrepared())
                                    <form method="POST" action="{{ route('admin.orders.ready', $order) }}" class="mt-6">
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit" class="app-button-primary">
                                            Marcar como listo
                                        </button>
                                    </form>
                                @elseif ($order->status->value === 'pending')
                                    <p class="mt-6 text-sm text-stone-500">
                                        Este pedido online sigue esperando confirmacion de pago antes de entrar en preparacion.
                                    </p>
                                @elseif ($order->status->value === 'ready')
                                    <form method="POST" action="{{ route('admin.orders.complete', $order) }}" class="mt-6">
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit" class="app-button-primary">
                                            Marcar como entregado
                                        </button>
                                    </form>
                                @endif
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
