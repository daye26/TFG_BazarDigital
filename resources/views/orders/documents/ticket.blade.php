<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Ticket {{ $order->order_number }}</title>
    <style>
        @page {
            margin: 6mm 4mm;
        }

        body {
            color: #111827;
            font-family: DejaVu Sans Mono, monospace;
            font-size: 10px;
            line-height: 1.45;
        }

        p {
            margin: 0;
        }

        .center {
            text-align: center;
        }

        .brand {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.16em;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        .muted {
            color: #4b5563;
        }

        .rule {
            border-top: 1px dashed #6b7280;
            margin: 10px 0;
        }

        .meta-table,
        .summary-table,
        .item-row {
            width: 100%;
        }

        .meta-table td,
        .summary-table td,
        .item-row td {
            padding: 2px 0;
            vertical-align: top;
        }

        .meta-table td:last-child,
        .summary-table td:last-child,
        .item-row td:last-child {
            text-align: right;
            white-space: nowrap;
        }

        .item {
            margin-bottom: 10px;
        }

        .item-title {
            font-weight: 700;
            margin-bottom: 2px;
        }

        .small {
            font-size: 9px;
        }

        .total {
            font-size: 12px;
            font-weight: 700;
        }

        .note {
            margin-top: 8px;
        }
    </style>
</head>
<body>
    @php
        $createdAtLabel = $order->created_at?->format('d/m/Y H:i') ?? 'Sin fecha';
        $paidAtLabel = $order->paid_at?->format('d/m/Y H:i');
        $customerPhone = $order->user?->phone_for_display ?? 'Sin telefono';
        $customerEmail = $order->user?->email ?? 'Sin correo';
    @endphp

    <div class="center">
        <p class="brand">Bazar Digital</p>
        <p>{{ $showCustomerMetadata ? 'Ticket interno' : 'Ticket de pedido' }}</p>
        <p class="muted small">Generado el {{ $generatedAt->format('d/m/Y H:i') }}</p>
    </div>

    <div class="rule"></div>

    <table class="meta-table">
        <tr>
            <td>Pedido</td>
            <td>{{ $order->order_number }}</td>
        </tr>
        <tr>
            <td>Estado</td>
            <td>{{ $order->status->label() }}</td>
        </tr>
        <tr>
            <td>Pago</td>
            <td>{{ $order->payment_status->label() }}</td>
        </tr>
        <tr>
            <td>Metodo</td>
            <td>{{ $order->payment_method->label() }}</td>
        </tr>
        <tr>
            <td>Recogida</td>
            <td>{{ $order->pickup_name }}</td>
        </tr>
        <tr>
            <td>Creado</td>
            <td>{{ $createdAtLabel }}</td>
        </tr>
        @if ($paidAtLabel)
            <tr>
                <td>Pagado</td>
                <td>{{ $paidAtLabel }}</td>
            </tr>
        @endif
        @if ($showCustomerMetadata)
            <tr>
                <td>Email</td>
                <td>{{ $customerEmail }}</td>
            </tr>
            <tr>
                <td>Telefono</td>
                <td>{{ $customerPhone }}</td>
            </tr>
        @endif
        @if ($order->payment_reference)
            <tr>
                <td>Ref.</td>
                <td>{{ $order->payment_reference }}</td>
            </tr>
        @endif
    </table>

    <div class="rule"></div>

    @foreach ($order->items as $item)
        <div class="item">
            <p class="item-title">{{ $item->product_name }}</p>
            <p class="muted small">{{ $item->product?->barcode ?? 'Snapshot' }} | IVA {{ $item->tax }}%</p>

            <table class="item-row">
                <tr>
                    <td>{{ $item->quantity }} x {{ number_format((float) $item->unit_final_price, 2, ',', '.') }} EUR</td>
                    <td>{{ number_format((float) $item->line_total, 2, ',', '.') }} EUR</td>
                </tr>
            </table>

            @if ($item->hasDiscount())
                <p class="muted small">Base {{ number_format((float) $item->unit_price, 2, ',', '.') }} EUR</p>
            @endif
        </div>
    @endforeach

    <div class="rule"></div>

    <table class="summary-table">
        <tr>
            <td>Subtotal</td>
            <td>{{ number_format((float) $order->subtotal, 2, ',', '.') }} EUR</td>
        </tr>
        @if ($order->hasDiscount())
            <tr>
                <td>Descuento</td>
                <td>-{{ number_format((float) $order->discount_total, 2, ',', '.') }} EUR</td>
            </tr>
        @endif
        <tr>
            <td>IVA</td>
            <td>{{ number_format((float) $order->tax_total, 2, ',', '.') }} EUR</td>
        </tr>
        <tr class="total">
            <td>Total</td>
            <td>{{ number_format((float) $order->total, 2, ',', '.') }} EUR</td>
        </tr>
    </table>

    @if ($order->notes)
        <div class="rule"></div>
        <p class="small"><strong>Notas</strong></p>
        <p class="note">{{ $order->notes }}</p>
    @endif

    @if ($order->status->value === 'cancelled' && $order->cancel_reason)
        <div class="rule"></div>
        <p class="small"><strong>Cancelacion</strong></p>
        <p class="note">{{ $order->cancel_reason }}</p>
    @endif

    <div class="rule"></div>

    <div class="center muted small">
        <p>Gracias por confiar en Bazar Digital.</p>
    </div>
</body>
</html>
