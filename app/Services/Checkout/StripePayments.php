<?php

namespace App\Services\Checkout;

use Stripe\PaymentIntent;
use Stripe\StripeClient;

class StripePayments
{
    public function create(array $parameters): PaymentIntent
    {
        return $this->client()->paymentIntents->create($parameters);
    }

    public function retrieve(string $id): PaymentIntent
    {
        return $this->client()->paymentIntents->retrieve($id);
    }

    private function client(): StripeClient
    {
        return new StripeClient(config('services.stripe.secret'));
    }
}
