<x-layouts.store title="Tus pedidos | Bazar Digital">
    <section class="store-shell pb-16 pt-10">
        <div class="store-toolbar">
            <div>
                <p class="store-kicker">Seguimiento de compra</p>
                <h1 class="store-heading">TUS PEDIDOS</h1>
                <p class="store-text mt-3 max-w-2xl">
                    Aqui puedes revisar el estado de cada pedido, ver si ya esta listo y, si sigue sin pagarse, mantener el pago online o pasarlo a pago en tienda.
                </p>
            </div>

            <a href="{{ route('products.index') }}" class="store-button-primary">
                Seguir comprando
            </a>
        </div>

        @if ($readyOrders->isNotEmpty())
            <div id="ready-orders" class="mt-8 space-y-4">
                <div class="store-toolbar">
                    <div>
                        <p class="store-kicker">Recogida</p>
                        <h2 class="store-title-lg">LISTOS PARA RECOGER</h2>
                    </div>
                </div>

                @foreach ($readyOrders as $order)
                    <article class="store-panel border-amber-300 bg-gradient-to-r from-amber-50 via-white to-amber-100 shadow-amber-200/70">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <p class="store-kicker text-stone-700">{{ $order->order_number }}</p>
                                <h2 class="mt-2 store-title-lg">{{ $order->pickup_name }}</h2>
                                <p class="store-text mt-3 text-stone-700">
                                    {{ $order->items_count }} lineas | {{ number_format((float) $order->total, 2, ',', '.') }} &euro;
                                </p>
                            </div>

                            <div class="flex flex-col gap-4 lg:items-end">
                                <div class="flex flex-wrap gap-2 lg:justify-end">
                                    <span class="store-status-pill store-status-pill-warning">
                                        LISTO PARA RECOGER
                                    </span>
                                    <span class="store-status-pill {{ $order->isPaid() ? 'store-status-pill-success' : 'store-status-pill-neutral' }}">
                                        Pago {{ $order->payment_status->label() }}
                                    </span>
                                </div>

                                <div class="flex flex-wrap gap-3 lg:justify-end">
                                    @if ($order->canRetryOnlinePayment())
                                        <form method="POST" action="{{ route('checkout.pay', $order) }}">
                                            @csrf

                                            <button type="submit" class="store-button-accent">
                                                Pago online
                                            </button>
                                        </form>

                                        <x-orders.switch-to-store-form
                                            :order="$order"
                                            button-class="store-button-secondary"
                                        />
                                    @endif

                                    <a href="{{ route('orders.show', $order) }}" class="store-button-secondary">
                                        Ver pedido
                                    </a>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        @if ($readyOrders->isEmpty() && $otherOrders->count() === 0)
            <div class="mt-8 store-empty">
                <p class="store-title-lg">Todavia no has hecho ningun pedido.</p>
                <p class="store-text mt-3">Cuando conviertas tu carrito en pedido, aparecera aqui con su estado y forma de pago.</p>
            </div>
        @endif

        @if ($otherOrders->count() > 0)
            <div class="{{ $readyOrders->isNotEmpty() ? 'mt-10' : 'mt-8' }} space-y-4">
                @if ($readyOrders->isNotEmpty())
                    <div class="store-toolbar">
                        <div>
                            <p class="store-kicker">Historial y seguimiento</p>
                            <h2 class="store-title-lg">RESTO DE PEDIDOS</h2>
                        </div>
                    </div>
                @endif

                @foreach ($otherOrders as $order)
                    <article class="store-panel">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <p class="store-kicker">{{ $order->order_number }}</p>
                                <h2 class="mt-2 store-title-lg">{{ $order->pickup_name }}</h2>
                                <p class="store-text mt-3">
                                    {{ $order->items_count }} lineas | {{ number_format((float) $order->total, 2, ',', '.') }} &euro;
                                </p>
                            </div>

                            <div class="flex flex-col gap-4 lg:items-end">
                                <div class="flex flex-wrap gap-2 lg:justify-end">
                                    <span class="store-status-pill {{ match ($order->status->value) { 'completed' => 'store-status-pill-success', 'cancelled' => 'store-status-pill-danger', default => 'store-status-pill-neutral' } }}">
                                        {{ $order->status->label() }}
                                    </span>
                                    <span class="store-status-pill {{ $order->isPaid() ? 'store-status-pill-success' : 'store-status-pill-neutral' }}">
                                        Pago {{ $order->payment_status->label() }}
                                    </span>
                                </div>

                                <div class="flex flex-wrap gap-3 lg:justify-end">
                                    @if ($order->canRetryOnlinePayment())
                                        <form method="POST" action="{{ route('checkout.pay', $order) }}">
                                            @csrf

                                            <button type="submit" class="store-button-primary-highlight">
                                                Pago online
                                            </button>
                                        </form>

                                        <x-orders.switch-to-store-form
                                            :order="$order"
                                            button-class="store-button-secondary"
                                        />
                                    @endif

                                    <a href="{{ route('orders.show', $order) }}" class="store-button-secondary">
                                        Ver pedido
                                    </a>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach

                @if ($otherOrders->hasPages())
                    <div class="pt-2">
                        {{ $otherOrders->links() }}
                    </div>
                @endif
            </div>
        @endif
    </section>
</x-layouts.store>
