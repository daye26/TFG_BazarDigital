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
    ) {}

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
            'metadata' => $this->normalizeMetadata($session->metadata ?? null),
        ]);
    }

    public function syncSuccessfulCheckoutPayload(array $sessionPayload): Order
    {
        $order = $this->resolveOrderFromSessionPayload($sessionPayload);

        return $this->syncSuccessfulCheckoutData($order, $sessionPayload);
    }

    protected function syncSuccessfulCheckoutData(Order $order, array $sessionPayload): Order
    {
        $metadata = $this->normalizeMetadata($sessionPayload['metadata'] ?? null);

        if (
            ! $this->sessionReferencesOrder($order, $sessionPayload, $metadata)
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

    /**
     * @return array<string, mixed>
     */
    protected function normalizeMetadata(mixed $metadata): array
    {
        if (is_array($metadata)) {
            return $metadata;
        }

        if (is_object($metadata) && method_exists($metadata, 'toArray')) {
            $metadata = $metadata->toArray();

            return is_array($metadata) ? $metadata : [];
        }

        if ($metadata instanceof \Traversable) {
            return iterator_to_array($metadata);
        }

        return [];
    }

    protected function resolveOrderFromSessionPayload(array $sessionPayload): Order
    {
        $metadata = $this->normalizeMetadata($sessionPayload['metadata'] ?? null);

        foreach ($this->sessionReferences($sessionPayload, $metadata) as $reference) {
            $order = Order::query()
                ->where(function ($query) use ($reference): void {
                    $query
                        ->whereKey($reference)
                        ->orWhere('order_number', $reference);
                })
                ->first();

            if ($order) {
                return $order;
            }
        }

        throw new RuntimeException('La sesion de Stripe no referencia ningun pedido.');
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    protected function sessionReferencesOrder(Order $order, array $sessionPayload, array $metadata): bool
    {
        $expectedReferences = array_filter([
            (string) $order->id,
            (string) $order->order_number,
        ]);

        foreach ($this->sessionReferences($sessionPayload, $metadata) as $reference) {
            if (in_array($reference, $expectedReferences, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return list<string>
     */
    protected function sessionReferences(array $sessionPayload, array $metadata): array
    {
        $references = [
            (string) ($metadata['order_id'] ?? ''),
            (string) ($sessionPayload['client_reference_id'] ?? ''),
            (string) ($metadata['order_number'] ?? ''),
        ];

        return array_values(array_unique(array_filter(
            $references,
            static fn (string $reference): bool => $reference !== ''
        )));
    }
}
