<x-layouts.store :title="$order->order_number . ' | Bazar Digital'">
    @php
        $cancelModalName = 'cancel-order-' . $order->getKey();
    @endphp

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
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                    <p class="store-cart-barcode">{{ $item->product?->barcode ?? 'Snapshot' }}</p>
                                    <span class="text-sm text-stone-300" aria-hidden="true">|</span>
                                    <p class="store-text">
                                        {{ $item->quantity }} unidades · {{ number_format((float) $item->unit_final_price, 2, ',', '.') }} &euro; por unidad
                                    </p>
                                </div>

                                <h2 class="mt-1 store-title-lg">{{ $item->product_name }}</h2>
                            </div>

                            <div class="store-cart-compact-actions lg:ml-auto lg:min-w-[18rem] lg:items-end lg:text-right">
                                <p class="store-detail-label">Total</p>

                                <div class="mt-0 flex w-full items-center justify-end gap-2">
                                    @if (((float) $item->unit_price * $item->quantity) > (float) $item->line_total)
                                        <p class="store-price-old">
                                            {{ number_format((float) $item->unit_price * $item->quantity, 2, ',', '.') }} &euro;
                                        </p>
                                    @endif

                                    <p class="store-cart-price">
                                        {{ number_format((float) $item->line_total, 2, ',', '.') }} &euro;
                                    </p>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <aside class="store-panel h-fit lg:sticky lg:top-6">
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
                    @if ($order->canRetryOnlinePayment())
                        <form method="POST" action="{{ route('checkout.pay', $order) }}">
                            @csrf

                            <button type="submit" class="store-button-primary w-full justify-center">
                                Pagar ahora con Stripe
                            </button>
                        </form>

                        <x-orders.switch-to-store-form
                            :order="$order"
                            button-class="store-button-secondary w-full justify-center"
                        />

                        <p class="store-text text-xs">
                            Si todavia no lo has pagado, puedes pasar este pedido a pago en tienda.
                        </p>
                    @endif

                    @if ($order->canBeCancelled())
                        <button
                            type="button"
                            x-data=""
                            x-on:click.prevent="$dispatch('open-modal', '{{ $cancelModalName }}')"
                            class="store-button-secondary w-full justify-center"
                        >
                            Cancelar pedido
                        </button>
                    @endif
                </div>

                @if ($order->canBeCancelled())
                    <x-modal :name="$cancelModalName" :show="$errors->has('cancel_reason')" maxWidth="lg" focusable>
                        <form method="POST" action="{{ route('orders.cancel', $order) }}" class="p-6 space-y-4">
                            @csrf
                            @method('PATCH')

                            <h3 class="store-title-lg">Cancelar pedido</h3>
                            <p class="store-text mt-3">
                                Indica el motivo de la cancelacion. Al confirmarla, el stock reservado volvera a estar disponible.
                            </p>

                            <div>
                                <label for="cancel_reason" class="store-detail-label">Motivo de cancelacion</label>
                                <textarea id="cancel_reason" name="cancel_reason" rows="4" class="form-textarea mt-2 w-full" required>{{ old('cancel_reason') }}</textarea>
                                <x-input-error :messages="$errors->get('cancel_reason')" class="mt-2" />
                            </div>

                            <div class="flex flex-wrap gap-3">
                                <button type="button" class="store-button-secondary" x-on:click="$dispatch('close')">
                                    Volver
                                </button>
                                <button type="submit" class="store-button-primary">
                                    Confirmar cancelacion
                                </button>
                            </div>
                        </form>
                    </x-modal>
                @endif
            </aside>
        </div>
    </section>
</x-layouts.store>
