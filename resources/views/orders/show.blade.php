<x-layouts.store :title="$order->order_number . ' | Bazar Digital'">
    <section class="store-shell pb-16 pt-10">
        <div class="store-toolbar">
            <div>
                <p class="store-kicker">Detalle del pedido</p>
                <h1 class="store-heading">{{ $order->order_number }}</h1>
                <p class="store-text mt-3 max-w-2xl">
                    Recogida a nombre de {{ $order->pickup_name }}.
                </p>
            </div>

            <a href="{{ route('orders.index') }}" class="store-button-secondary">
                Volver a pedidos
            </a>
        </div>

        @if (session('order_status'))
            <div class="app-alert-success mt-6 mb-0">
                {{ session('order_status') }}
            </div>
        @endif

        <div class="mt-8 grid gap-6 lg:grid-cols-[1.18fr_0.82fr]">
            <div class="space-y-4">
                @foreach ($order->items as $item)
                    <article class="store-card store-cart-compact-card">
                        <div class="store-cart-compact-layout">
                            <div class="min-w-0">
                                <p class="store-cart-barcode">{{ $item->product?->barcode ?? 'Snapshot' }}</p>
                                <h2 class="mt-2 store-title-lg">{{ $item->product_name }}</h2>
                                <p class="store-text mt-3">
                                    {{ $item->quantity }} unidades · {{ number_format((float) $item->unit_final_price, 2, ',', '.') }} &euro; por unidad
                                </p>
                            </div>

                            <div class="store-cart-compact-actions">
                                <p class="store-detail-label">Total linea</p>
                                <p class="store-cart-price">
                                    {{ number_format((float) $item->line_total, 2, ',', '.') }} &euro;
                                </p>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <aside class="store-panel h-fit lg:sticky lg:top-6" x-data="{ cancelOpen: false }">
                <p class="store-kicker">Resumen</p>
                <h2 class="store-heading">ESTADO ACTUAL</h2>

                <div class="mt-6 flex flex-wrap gap-2">
                    <span class="store-status-pill {{ match ($order->status->value) { 'ready' => 'store-status-pill-warning', 'completed' => 'store-status-pill-success', 'cancelled' => 'store-status-pill-danger', default => 'store-status-pill-neutral' } }}">
                        {{ $order->status->label() }}
                    </span>
                    <span class="store-status-pill {{ $order->isPaid() ? 'store-status-pill-success' : 'store-status-pill-neutral' }}">
                        Pago {{ $order->payment_status->label() }}
                    </span>
                </div>

                <div class="mt-8 space-y-4">
                    <div class="flex items-center justify-between gap-4 text-sm text-stone-600">
                        <span>Metodo de pago</span>
                        <span class="font-semibold text-stone-900">{{ $order->payment_method->label() }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4 text-sm text-stone-600">
                        <span>Subtotal</span>
                        <span class="font-semibold text-stone-900">{{ number_format((float) $order->subtotal, 2, ',', '.') }} &euro;</span>
                    </div>
                    <div class="flex items-center justify-between gap-4 text-sm text-stone-600">
                        <span>Descuento</span>
                        <span class="font-semibold text-stone-900">-{{ number_format((float) $order->discount_total, 2, ',', '.') }} &euro;</span>
                    </div>
                    <div class="flex items-center justify-between gap-4 text-sm text-stone-600">
                        <span>IVA incluido</span>
                        <span class="font-semibold text-stone-900">{{ number_format((float) $order->tax_total, 2, ',', '.') }} &euro;</span>
                    </div>
                    <div class="flex items-center justify-between gap-4 border-t border-stone-200 pt-4 text-base font-bold text-stone-950">
                        <span>Total</span>
                        <span>{{ number_format((float) $order->total, 2, ',', '.') }} &euro;</span>
                    </div>
                </div>

                @if ($order->status->value === 'cancelled' && $order->cancel_reason)
                    <div class="app-alert-error mt-6 mb-0">
                        Motivo de cancelacion: {{ $order->cancel_reason }}
                    </div>
                @endif

                <div class="mt-8 flex flex-col gap-3">
                    @if ($order->usesOnlinePayment() && ! $order->isPaid() && $order->status->value !== 'cancelled' && $order->status->value !== 'completed')
                        <form method="POST" action="{{ route('checkout.pay', $order) }}">
                            @csrf

                            <button type="submit" class="store-button-primary w-full justify-center">
                                Pagar ahora con Stripe
                            </button>
                        </form>
                    @endif

                    @if ($order->canBeCancelled())
                        <button type="button" class="store-button-secondary w-full justify-center" @click="cancelOpen = true">
                            Cancelar pedido
                        </button>
                    @endif
                </div>

                @if ($order->canBeCancelled())
                    <div x-cloak x-show="cancelOpen" class="store-modal-backdrop" @click.self="cancelOpen = false">
                        <div class="store-modal-card">
                            <h3 class="store-title-lg">Cancelar pedido</h3>
                            <p class="store-text mt-3">
                                Indica el motivo de la cancelacion. Al confirmarla, el stock reservado volvera a estar disponible.
                            </p>

                            <form method="POST" action="{{ route('orders.cancel', $order) }}" class="mt-6 space-y-4">
                                @csrf
                                @method('PATCH')

                                <div>
                                    <label for="cancel_reason" class="store-detail-label">Motivo de cancelacion</label>
                                    <textarea id="cancel_reason" name="cancel_reason" rows="4" class="form-textarea mt-2 w-full" required>{{ old('cancel_reason') }}</textarea>
                                </div>

                                <div class="flex flex-wrap gap-3">
                                    <button type="button" class="store-button-secondary" @click="cancelOpen = false">
                                        Volver
                                    </button>
                                    <button type="submit" class="store-button-primary">
                                        Confirmar cancelacion
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </section>
</x-layouts.store>
