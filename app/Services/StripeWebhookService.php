<?php

namespace App\Services;

use InvalidArgumentException;
use JsonException;
use RuntimeException;

class StripeWebhookService
{
    protected const SIGNATURE_TOLERANCE = 300;

    public function __construct(
        protected StripeCheckoutService $stripeCheckoutService,
        protected StripeService $stripeService,
    ) {
    }

    public function handle(string $payload, ?string $signatureHeader): void
    {
        $event = $this->parseVerifiedEvent($payload, $signatureHeader);

        if (! in_array($event['type'] ?? null, [
            'checkout.session.completed',
            'checkout.session.async_payment_succeeded',
        ], true)) {
            return;
        }

        $sessionPayload = $event['data']['object'] ?? null;

        if (! is_array($sessionPayload)) {
            throw new InvalidArgumentException('El payload del webhook de Stripe no incluye una sesión válida.');
        }

        if (($sessionPayload['payment_status'] ?? null) !== 'paid') {
            return;
        }

        $this->stripeCheckoutService->syncSuccessfulCheckoutPayload($sessionPayload);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws JsonException
     */
    protected function parseVerifiedEvent(string $payload, ?string $signatureHeader): array
    {
        $secret = $this->stripeService->webhookSecret();

        if (! filled($secret)) {
            throw new RuntimeException('Stripe webhook secret is not configured.');
        }

        if (! filled($signatureHeader)) {
            throw new InvalidArgumentException('Falta la cabecera Stripe-Signature.');
        }

        if ($payload === '') {
            throw new InvalidArgumentException('El payload del webhook de Stripe esta vacio.');
        }

        $this->assertValidSignature($payload, $signatureHeader, $secret);

        $event = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($event)) {
            throw new InvalidArgumentException('El payload del webhook de Stripe no es valido.');
        }

        return $event;
    }

    protected function assertValidSignature(string $payload, string $signatureHeader, string $secret): void
    {
        $timestamp = null;
        $signatures = [];

        foreach (explode(',', $signatureHeader) as $segment) {
            [$key, $value] = array_pad(explode('=', trim($segment), 2), 2, null);

            if ($key === 't' && filled($value)) {
                $timestamp = (int) $value;
            }

            if ($key === 'v1' && filled($value)) {
                $signatures[] = $value;
            }
        }

        if (! $timestamp || $signatures === []) {
            throw new InvalidArgumentException('La cabecera Stripe-Signature no es valida.');
        }

        if (abs(now()->timestamp - $timestamp) > self::SIGNATURE_TOLERANCE) {
            throw new InvalidArgumentException('La firma del webhook de Stripe ha caducado.');
        }

        $expectedSignature = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

        foreach ($signatures as $signature) {
            if (hash_equals($expectedSignature, $signature)) {
                return;
            }
        }

        throw new InvalidArgumentException('No se ha podido verificar la firma del webhook de Stripe.');
    }
}
