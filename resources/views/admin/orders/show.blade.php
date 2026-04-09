<x-app-layout>
    <x-slot name="header">
        <x-admin.panel-header
            title="Detalle del pedido"
            active="orders"
            :back-href="$backUrl"
            back-label="Volver a pedidos"
        />
    </x-slot>

    <div class="app-page">
        <div class="app-shell-stack">
            <section class="app-surface">
                <div class="app-hero">
                    <p class="app-hero-kicker">Pedido administracion</p>
                    <h3 class="app-hero-title">{{ $order->order_number }}</h3>
                    <p class="app-hero-copy">
                        Recogida a nombre de {{ $order->pickup_name }}. Desde aqui puedes revisar el contenido completo del pedido sin salir del panel.
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

                    <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                        <div class="flex flex-wrap gap-2">
                            <span class="store-status-pill {{ match ($order->status->value) { 'ready' => 'store-status-pill-warning', 'completed' => 'store-status-pill-success', 'cancelled' => 'store-status-pill-danger', default => 'store-status-pill-neutral' } }}">
                                {{ $order->status->label() }}
                            </span>
                            <span class="store-status-pill {{ $order->isPaid() ? 'store-status-pill-success' : 'store-status-pill-neutral' }}">
                                Pago {{ $order->payment_status->label() }}
                            </span>
                            <span class="store-status-pill store-status-pill-neutral">
                                {{ $order->payment_method->label() }}
                            </span>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            @if ($order->status->value === 'pending' && $order->canBePrepared())
                                <form method="POST" action="{{ route('admin.orders.ready', $order) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="return_context" value="show">
                                    <input type="hidden" name="return_scope" value="{{ $returnScope }}">
                                    <input type="hidden" name="return_q" value="{{ $returnSearchQuery }}">
                                    <input type="hidden" name="return_date" value="{{ $returnSelectedDate ?? '' }}">
                                    <input type="hidden" name="return_page" value="{{ $returnPage }}">

                                    <button type="submit" class="app-button-primary">
                                        Marcar como listo
                                    </button>
                                </form>
                            @elseif ($order->status->value === 'ready')
                                <form method="POST" action="{{ route('admin.orders.complete', $order) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="return_context" value="show">
                                    <input type="hidden" name="return_scope" value="{{ $returnScope }}">
                                    <input type="hidden" name="return_q" value="{{ $returnSearchQuery }}">
                                    <input type="hidden" name="return_date" value="{{ $returnSelectedDate ?? '' }}">
                                    <input type="hidden" name="return_page" value="{{ $returnPage }}">

                                    <button type="submit" class="app-button-primary">
                                        Marcar como entregado
                                    </button>
                                </form>
                            @endif

                            <a href="{{ $backUrl }}" class="app-button-secondary">
                                Volver al listado
                            </a>
                        </div>
                    </div>

                    @if ($order->status->value === 'pending' && ! $order->canBePrepared())
                        <p class="mt-6 text-sm text-stone-500">
                            Este pedido online sigue esperando confirmacion de pago antes de entrar en preparacion.
                        </p>
                    @endif

                    <div class="mt-8">
                        <x-admin.order-items-panel :order="$order" />
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
