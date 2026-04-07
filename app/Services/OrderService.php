<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrderService
{
    public function placeFromCart(User $user, string $pickupName, PaymentMethod $paymentMethod): Order
    {
        return DB::transaction(function () use ($user, $pickupName, $paymentMethod): Order {
            $freshUser = User::query()
                ->with('cartItems')
                ->findOrFail($user->id);

            $cartItems = $freshUser->cartItems;

            if ($cartItems->isEmpty()) {
                throw new RuntimeException('Tu carrito esta vacio.');
            }

            $products = Product::query()
                ->whereKey($cartItems->pluck('product_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0.0;
            $discountTotal = 0.0;
            $taxTotal = 0.0;
            $total = 0.0;
            $lines = [];

            foreach ($cartItems as $item) {
                $product = $products->get($item->product_id);

                if (! $product || ! $product->is_active) {
                    throw new RuntimeException('Uno de los productos del carrito ya no esta disponible.');
                }

                if ($product->qty < $item->quantity) {
                    throw new RuntimeException("No hay stock suficiente para {$product->name}.");
                }

                $unitPrice = (float) $product->sale_price;
                $unitFinalPrice = (float) $product->discounted_price;
                $lineSubtotal = round($unitPrice * $item->quantity, 2);
                $lineTotal = round($unitFinalPrice * $item->quantity, 2);
                $lineTax = $this->calculateTaxAmount($lineTotal, $product->tax);

                $subtotal += $lineSubtotal;
                $discountTotal += max($lineSubtotal - $lineTotal, 0);
                $taxTotal += $lineTax;
                $total += $lineTotal;

                $lines[] = [
                    'product' => $product,
                    'payload' => [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $item->quantity,
                        'tax' => $product->tax,
                        'unit_price' => number_format($unitPrice, 2, '.', ''),
                        'discount_type' => $product->has_discount ? $product->discount_type : null,
                        'discount_value' => number_format((float) $product->discount_value, 2, '.', ''),
                        'unit_final_price' => number_format($unitFinalPrice, 2, '.', ''),
                        'line_total' => number_format($lineTotal, 2, '.', ''),
                    ],
                ];
            }

            $order = $freshUser->orders()->create([
                'source' => 'web',
                'pickup_name' => trim($pickupName),
                'status' => OrderStatus::PENDING,
                'payment_method' => $paymentMethod,
                'payment_status' => PaymentStatus::PENDING,
                'subtotal' => number_format($subtotal, 2, '.', ''),
                'discount_total' => number_format($discountTotal, 2, '.', ''),
                'tax_total' => number_format($taxTotal, 2, '.', ''),
                'total' => number_format($total, 2, '.', ''),
            ]);

            $order->items()->createMany(array_column($lines, 'payload'));

            foreach ($lines as $line) {
                $line['product']->decrement('qty', $line['payload']['quantity']);
            }

            $order->forceFill([
                'order_number' => $this->generateOrderNumber($order),
            ])->save();

            return $order->fresh(['items.product', 'user']);
        });
    }

    public function clearCart(User $user): void
    {
        $user->cartItems()->delete();
        $user->forceFill([
            'cart_created_at' => null,
        ])->save();
    }

    public function cancel(Order $order, string $reason): Order
    {
        return DB::transaction(function () use ($order, $reason): Order {
            $lockedOrder = Order::query()
                ->with(['items.product'])
                ->lockForUpdate()
                ->findOrFail($order->id);

            if (! $lockedOrder->canBeCancelled()) {
                throw new RuntimeException('Este pedido ya no se puede cancelar.');
            }

            foreach ($lockedOrder->items as $item) {
                if ($item->product) {
                    $item->product->increment('qty', $item->quantity);
                }
            }

            $lockedOrder->forceFill([
                'status' => OrderStatus::CANCELLED,
                'cancel_reason' => trim($reason),
            ])->save();

            return $lockedOrder->fresh(['items.product', 'user']);
        });
    }

    public function markReady(Order $order): Order
    {
        return DB::transaction(function () use ($order): Order {
            $lockedOrder = Order::query()
                ->lockForUpdate()
                ->findOrFail($order->id);

            if (! $lockedOrder->canBePrepared()) {
                throw new RuntimeException('Este pedido aun no se puede preparar.');
            }

            if ($lockedOrder->status !== OrderStatus::PENDING) {
                throw new RuntimeException('Solo se pueden marcar como listos los pedidos pendientes.');
            }

            $lockedOrder->forceFill([
                'status' => OrderStatus::READY,
            ])->save();

            return $lockedOrder->fresh(['items.product', 'user']);
        });
    }

    public function markCompleted(Order $order): Order
    {
        return DB::transaction(function () use ($order): Order {
            $lockedOrder = Order::query()
                ->lockForUpdate()
                ->findOrFail($order->id);

            if ($lockedOrder->status !== OrderStatus::READY) {
                throw new RuntimeException('Solo se pueden completar pedidos que esten listos.');
            }

            $payload = [
                'status' => OrderStatus::COMPLETED,
            ];

            if ($lockedOrder->usesStorePayment() && ! $lockedOrder->isPaid()) {
                $payload['payment_status'] = PaymentStatus::PAID;
                $payload['paid_at'] = now();
            }

            $lockedOrder->forceFill($payload)->save();

            return $lockedOrder->fresh(['items.product', 'user']);
        });
    }

    public function rollbackDraftOnlineOrder(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $lockedOrder = Order::query()
                ->with(['items.product'])
                ->lockForUpdate()
                ->findOrFail($order->id);

            foreach ($lockedOrder->items as $item) {
                if ($item->product) {
                    $item->product->increment('qty', $item->quantity);
                }
            }

            $lockedOrder->items()->delete();
            $lockedOrder->delete();
        });
    }

    protected function generateOrderNumber(Order $order): string
    {
        return sprintf(
            'WEB-%s-%06d',
            $order->created_at->format('YmdHis'),
            $order->id
        );
    }

    protected function calculateTaxAmount(float $lineTotal, int $taxRate): float
    {
        if ($taxRate <= 0) {
            return 0.0;
        }

        return round($lineTotal - ($lineTotal / (1 + ($taxRate / 100))), 2);
    }
}
