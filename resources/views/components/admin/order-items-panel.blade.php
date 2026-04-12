@props([
    'order',
    'showMeta' => true,
])

@php
    $createdAtLabel = $order->created_at?->format('d/m/Y H:i') ?? 'Sin fecha';
    $totalUnits = (int) $order->items->sum('quantity');
@endphp

<div class="space-y-5">
    @if ($showMeta)
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="app-mini-card">
                <p class="app-stat-label">Recogida</p>
                <p class="mt-3 text-lg font-black tracking-tight text-stone-950">
                    {{ $order->pickup_name }}
                </p>
                <p class="mt-2 text-sm text-stone-500">Creado el {{ $createdAtLabel }}</p>
            </article>

            <article class="app-mini-card">
                <p class="app-stat-label">Telefono</p>
                <p class="mt-3 text-lg font-black tracking-tight text-stone-950">
                    {{ $order->user?->phone_for_display ?? 'Sin telefono asociado' }}
                </p>
                <p class="mt-2 text-sm text-stone-500">{{ $order->user?->email ?? 'Sin correo asociado' }}</p>
            </article>

            <article class="app-mini-card">
                <p class="app-stat-label">Pago</p>
                <p class="mt-3 text-lg font-black tracking-tight text-stone-950">{{ $order->payment_method->label() }}</p>
                <p class="mt-2 text-sm text-stone-500">Estado {{ $order->payment_status->label() }}</p>
            </article>

            <article class="app-mini-card">
                <p class="app-stat-label">Contenido</p>
                <p class="mt-3 text-lg font-black tracking-tight text-stone-950">{{ $order->items->count() }} lineas</p>
                <p class="mt-2 text-sm text-stone-500">{{ $totalUnits }} unidad{{ $totalUnits === 1 ? '' : 'es' }}</p>
            </article>
        </section>
    @endif

    <section class="app-note-card">
        <div class="app-order-lines-panel">
            @foreach ($order->items as $item)
                @php
                    $discountLabel = match ($item->discount_type) {
                        'percentage' => 'Desc. ' . rtrim(rtrim(number_format((float) $item->discount_value, 2, ',', ''), '0'), ',') . '%',
                        'fixed' => 'Desc. -' . number_format((float) $item->discount_value, 2, ',', '.') . ' EUR',
                        default => null,
                    };

                    $lineMeta = collect([
                        $item->product?->barcode ?? 'Snapshot sin producto enlazado',
                        'IVA ' . $item->tax . '%',
                        $discountLabel,
                    ])->filter()->implode(' / ');
                @endphp

                <article class="app-order-line-row">
                    <div class="app-order-line-main">
                        <div class="flex flex-wrap items-center gap-4">
                            <div class="flex flex-wrap items-center gap-3">
                                <p class="app-order-line-title">{{ $item->product_name }} |</p>
                                <p class="app-order-line-title">{{ $item->quantity }} uds</p>
                            </div>
                            @if ($item->product)
                                <a
                                    href="{{ route('admin.products.manage', ['product' => $item->product->id]) }}"
                                    class="app-order-line-link"
                                >
                                    Abrir producto
                                </a>
                            @endif
                        </div>

                        <p class="app-order-line-meta">{{ $lineMeta }}</p>
                    </div>

                    <div class="app-order-line-pricing">
                        <p class="app-stat-label">
                            {{ $item->quantity }} x {{ number_format((float) $item->unit_final_price, 2, ',', '.') }} &euro;
                        </p>

                        @if ((float) $item->unit_price !== (float) $item->unit_final_price)
                            <p class="text-xs text-stone-500">
                                Base {{ number_format((float) $item->unit_price, 2, ',', '.') }} &euro;
                            </p>
                        @endif

                        <p class="app-order-line-total">
                            {{ number_format((float) $item->line_total, 2, ',', '.') }} &euro;
                        </p>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(320px,0.7fr)]">
        <article class="app-note-card">
            <p class="app-note-kicker">Datos del pedido</p>
            <div class="app-note-copy">
                <p>Numero de pedido: {{ $order->order_number }}</p>
                <p>Origen: {{ strtoupper($order->source) }}</p>

                @if ($order->payment_reference)
                    <p>Referencia de pago: {{ $order->payment_reference }}</p>
                @endif

                @if ($order->notes)
                    <p>Notas: {{ $order->notes }}</p>
                @endif

                @if ($order->status->value === 'cancelled' && $order->cancel_reason)
                    <p class="font-medium text-rose-700">Motivo de cancelacion: {{ $order->cancel_reason }}</p>
                @endif
            </div>
        </article>

        <article class="app-card-muted app-order-summary-panel">
            <p class="app-section-kicker">Resumen</p>
            <dl class="app-summary-list mt-4">
                <div class="app-summary-row">
                    <dt>Subtotal</dt>
                    <dd class="app-summary-value">{{ number_format((float) $order->subtotal, 2, ',', '.') }} &euro;</dd>
                </div>
                <div class="app-summary-row">
                    <dt>Descuento</dt>
                    <dd class="app-summary-value">-{{ number_format((float) $order->discount_total, 2, ',', '.') }} &euro;</dd>
                </div>
                <div class="app-summary-row">
                    <dt>IVA incluido</dt>
                    <dd class="app-summary-value">{{ number_format((float) $order->tax_total, 2, ',', '.') }} &euro;</dd>
                </div>
                <div class="app-summary-total-row">
                    <dt class="app-summary-total-label">Total</dt>
                    <dd class="app-summary-total-value">{{ number_format((float) $order->total, 2, ',', '.') }} &euro;</dd>
                </div>
            </dl>
        </article>
    </section>
</div>
