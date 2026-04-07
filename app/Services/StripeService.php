<?php

namespace App\Services;

use RuntimeException;
use Stripe\StripeClient;

class StripeService
{
    public function isConfigured(): bool
    {
        return filled(config('services.stripe.secret'));
    }

    public function client(): StripeClient
    {
        $secret = config('services.stripe.secret');

        if (! filled($secret)) {
            throw new RuntimeException('Stripe secret key is not configured.');
        }

        return new StripeClient($secret);
    }

    public function publicKey(): ?string
    {
        return config('services.stripe.key');
    }

    public function webhookSecret(): ?string
    {
        return config('services.stripe.webhook_secret');
    }

    public function currency(): string
    {
        return (string) config('services.stripe.currency', 'eur');
    }

    /**
     * @return array{success_url:?string,cancel_url:?string}
     */
    public function checkoutUrls(): array
    {
        return [
            'success_url' => config('services.stripe.checkout.success_url'),
            'cancel_url' => config('services.stripe.checkout.cancel_url'),
        ];
    }
}
