<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Stripe\Checkout\Session;

class StripeCheckoutService
{
    public function __construct(
        protected StripeService $stripeService,
    ) {
    }

    public function createSession(Order $order): Session
    {
        if ($order->payment_method !== PaymentMethod::ONLINE) {
            throw new RuntimeException('Solo se pueden crear sesiones de Stripe para pedidos online.');
        }

        $client = $this->stripeService->client();
        $urls = $this->resolveCheckoutUrls($order);

        $session = $client->checkout->sessions->create([
            'mode' => 'payment',
            'customer_email' => $order->user?->email,
            'client_reference_id' => (string) $order->id,
            'success_url' => $urls['success_url'],
            'cancel_url' => $urls['cancel_url'],
            'payment_method_types' => ['card'],
            'metadata' => [
                'order_id' => (string) $order->id,
                'order_number' => (string) $order->order_number,
            ],
            'line_items' => $order->items->map(function ($item): array {
                return [
                    'quantity' => $item->quantity,
                    'price_data' => [
                        'currency' => $this->stripeService->currency(),
                        'unit_amount' => (int) round(((float) $item->unit_final_price) * 100),
                        'product_data' => [
                            'name' => $item->product_name,
                        ],
                    ],
                ];
            })->all(),
        ]);

        $order->forceFill([
            'payment_reference' => $session->id,
        ])->save();

        return $session;
    }

    public function syncSuccessfulCheckout(Order $order, string $sessionId): Order
    {
        $client = $this->stripeService->client();
        $session = $client->checkout->sessions->retrieve($sessionId);

        return $this->syncSuccessfulCheckoutData($order, [
            'id' => $session->id,
            'client_reference_id' => $session->client_reference_id,
            'payment_intent' => $session->payment_intent,
            'payment_status' => $session->payment_status,
            'metadata' => (array) $session->metadata,
        ]);
    }

    public function syncSuccessfulCheckoutPayload(array $sessionPayload): Order
    {
        $orderId = $sessionPayload['metadata']['order_id'] ?? $sessionPayload['client_reference_id'] ?? null;

        if (! filled((string) $orderId)) {
            throw new RuntimeException('La sesion de Stripe no referencia ningun pedido.');
        }

        $order = Order::query()->findOrFail($orderId);

        return $this->syncSuccessfulCheckoutData($order, $sessionPayload);
    }

    protected function syncSuccessfulCheckoutData(Order $order, array $sessionPayload): Order
    {
        if (
            ($sessionPayload['metadata']['order_id'] ?? null) !== (string) $order->id
            || ($sessionPayload['payment_status'] ?? null) !== 'paid'
        ) {
            throw new RuntimeException('El pago todavia no figura como completado en Stripe.');
        }

        return DB::transaction(function () use ($order, $sessionPayload): Order {
            $lockedOrder = Order::query()
                ->lockForUpdate()
                ->findOrFail($order->id);

            if ($lockedOrder->payment_method !== PaymentMethod::ONLINE) {
                throw new RuntimeException('Solo se pueden confirmar pagos de Stripe para pedidos online.');
            }

            $lockedOrder->forceFill([
                'payment_status' => PaymentStatus::PAID,
                'paid_at' => $lockedOrder->paid_at ?? now(),
                'payment_reference' => $sessionPayload['payment_intent'] ?: $sessionPayload['id'],
            ])->save();

            return $lockedOrder->fresh(['items.product', 'user']);
        });
    }

    /**
     * @return array{success_url:string,cancel_url:string}
     */
    protected function resolveCheckoutUrls(Order $order): array
    {
        $configuredUrls = $this->stripeService->checkoutUrls();

        return [
            'success_url' => $configuredUrls['success_url']
                ? $configuredUrls['success_url'].'?session_id={CHECKOUT_SESSION_ID}&order='.$order->id
                : route('checkout.success', ['order' => $order]).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $configuredUrls['cancel_url']
                ? $configuredUrls['cancel_url'].'?order='.$order->id
                : route('checkout.cancel', ['order' => $order]),
        ];
    }
}
