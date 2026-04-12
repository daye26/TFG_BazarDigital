<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AdminAlertService
{
    public const LOW_STOCK_THRESHOLD = 5;

    public const OVERDUE_PREPARABLE_HOURS = 24;

    public const STALE_READY_HOURS = 48;

    private const PREVIEW_LIMIT = 3;

    public function dashboardAlerts(): Collection
    {
        return collect([
            $this->buildOverduePreparableOrdersAlert(),
            $this->buildOutOfStockProductsAlert(),
            $this->buildLowStockProductsAlert(),
            $this->buildStaleReadyOrdersAlert(),
        ])->filter()->values();
    }

    private function buildOverduePreparableOrdersAlert(): ?array
    {
        $baseQuery = Order::query()
            ->where('status', OrderStatus::PENDING)
            ->where(function ($query) {
                $query
                    ->where('payment_method', PaymentMethod::STORE->value)
                    ->orWhere('payment_status', PaymentStatus::PAID->value);
            })
            ->where('created_at', '<=', now()->subHours(self::OVERDUE_PREPARABLE_HOURS));

        $count = (clone $baseQuery)->count();

        if ($count === 0) {
            return null;
        }

        $items = (clone $baseQuery)
            ->oldest('created_at')
            ->take(self::PREVIEW_LIMIT)
            ->get()
            ->map(fn (Order $order) => [
                'title' => $order->order_number,
                'subtitle' => $order->pickup_name,
                'meta' => $this->elapsedLabel($order->created_at) . ' sin preparar',
                'href' => route('admin.orders.show', [
                    'order' => $order,
                    'scope' => 'overdue_preparable',
                ]),
            ])
            ->all();

        return [
            'key' => 'overdue_preparable_orders',
            'tone' => 'urgent',
            'tone_label' => 'Urgente',
            'title' => 'Pedidos por preparar +24h',
            'summary' => $this->summaryLabel($count, 'pedido supera ya las 24 horas de espera.', 'pedidos superan ya las 24 horas de espera.'),
            'count' => $count,
            'action_label' => 'Ver pedidos',
            'action_href' => route('admin.orders.index', ['scope' => 'overdue_preparable']),
            'items' => $items,
        ];
    }

    private function buildOutOfStockProductsAlert(): ?array
    {
        $baseQuery = Product::query()
            ->where('is_active', true)
            ->where('qty', '<=', 0);

        $count = (clone $baseQuery)->count();

        if ($count === 0) {
            return null;
        }

        $items = (clone $baseQuery)
            ->with('category')
            ->orderBy('name')
            ->take(self::PREVIEW_LIMIT)
            ->get()
            ->map(fn (Product $product) => [
                'title' => $product->name,
                'subtitle' => $product->category?->name ?? 'Sin categoria',
                'meta' => 'Stock 0 uds',
                'href' => route('admin.products.manage', [
                    'stock' => 'out',
                    'product' => $product->id,
                ]),
            ])
            ->all();

        return [
            'key' => 'out_of_stock_products',
            'tone' => 'urgent',
            'tone_label' => 'Urgente',
            'title' => 'Productos sin stock',
            'summary' => $this->summaryLabel($count, 'producto activo necesita reposicion inmediata.', 'productos activos necesitan reposicion inmediata.'),
            'count' => $count,
            'action_label' => 'Ver productos',
            'action_href' => route('admin.products.manage', ['stock' => 'out']),
            'items' => $items,
        ];
    }

    private function buildLowStockProductsAlert(): ?array
    {
        $baseQuery = Product::query()
            ->where('is_active', true)
            ->whereBetween('qty', [1, self::LOW_STOCK_THRESHOLD]);

        $count = (clone $baseQuery)->count();

        if ($count === 0) {
            return null;
        }

        $items = (clone $baseQuery)
            ->with('category')
            ->orderBy('qty')
            ->orderBy('name')
            ->take(self::PREVIEW_LIMIT)
            ->get()
            ->map(fn (Product $product) => [
                'title' => $product->name,
                'subtitle' => $product->category?->name ?? 'Sin categoria',
                'meta' => 'Stock ' . $product->qty . ' uds',
                'href' => route('admin.products.manage', [
                    'stock' => 'low',
                    'product' => $product->id,
                ]),
            ])
            ->all();

        return [
            'key' => 'low_stock_products',
            'tone' => 'review',
            'tone_label' => 'Revisar',
            'title' => 'Productos con stock bajo',
            'summary' => $this->summaryLabel($count, 'producto activo ha bajado del umbral de ' . self::LOW_STOCK_THRESHOLD . ' uds.', 'productos activos han bajado del umbral de ' . self::LOW_STOCK_THRESHOLD . ' uds.'),
            'count' => $count,
            'action_label' => 'Ver productos',
            'action_href' => route('admin.products.manage', ['stock' => 'low']),
            'items' => $items,
        ];
    }

    private function buildStaleReadyOrdersAlert(): ?array
    {
        $baseQuery = Order::query()
            ->where('status', OrderStatus::READY)
            ->where('updated_at', '<=', now()->subHours(self::STALE_READY_HOURS));

        $count = (clone $baseQuery)->count();

        if ($count === 0) {
            return null;
        }

        $items = (clone $baseQuery)
            ->oldest('updated_at')
            ->take(self::PREVIEW_LIMIT)
            ->get()
            ->map(fn (Order $order) => [
                'title' => $order->order_number,
                'subtitle' => $order->pickup_name,
                'meta' => $this->elapsedLabel($order->updated_at) . ' listo',
                'href' => route('admin.orders.show', [
                    'order' => $order,
                    'scope' => 'stale_ready',
                ]),
            ])
            ->all();

        return [
            'key' => 'stale_ready_orders',
            'tone' => 'review',
            'tone_label' => 'Seguimiento',
            'title' => 'Pedidos listos +48h',
            'summary' => $this->summaryLabel($count, 'pedido sigue listo para recoger desde hace mas de 48 horas.', 'pedidos siguen listos para recoger desde hace mas de 48 horas.'),
            'count' => $count,
            'action_label' => 'Ver pedidos',
            'action_href' => route('admin.orders.index', ['scope' => 'stale_ready']),
            'items' => $items,
        ];
    }

    private function summaryLabel(int $count, string $singular, string $plural): string
    {
        return $count . ' ' . ($count === 1 ? $singular : $plural);
    }

    private function elapsedLabel(?Carbon $date): string
    {
        if (! $date) {
            return 'Sin fecha';
        }

        $hours = max($date->diffInHours(now()), 1);

        if ($hours < 48) {
            return 'Hace ' . $hours . ' h';
        }

        $days = (int) floor($hours / 24);

        return 'Hace ' . $days . ' dia' . ($days === 1 ? '' : 's');
    }
}
