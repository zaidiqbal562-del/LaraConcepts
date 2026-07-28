<?php

namespace App\Services;

use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

class StripeService
{
    protected $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(env('STRIPE_SECRET'));
    }

    /**
     * Create a payment intent for a product purchase
     *
     * @param float $amount Amount in USD
     * @param string $currency Currency code (default: usd)
     * @param array $metadata Additional metadata
     * @return array Payment intent data
     */
    public function createPaymentIntent(float $amount, string $currency = 'usd', array $metadata = []): array
    {
        try {
            $amountInCents = intval($amount * 100);

            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => $amountInCents,
                'currency' => $currency,
                'payment_method_types' => ['card'],
                'metadata' => $metadata,
            ]);

            return [
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
                'amount' => $paymentIntent->amount,
                'currency' => $paymentIntent->currency,
                'status' => $paymentIntent->status,
            ];
        } catch (ApiErrorException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Retrieve a payment intent
     *
     * @param string $paymentIntentId
     * @return array
     */
    public function getPaymentIntent(string $paymentIntentId): array
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentIntentId);

            return [
                'success' => true,
                'id' => $paymentIntent->id,
                'status' => $paymentIntent->status,
                'amount' => $paymentIntent->amount,
                'currency' => $paymentIntent->currency,
                'client_secret' => $paymentIntent->client_secret,
            ];
        } catch (ApiErrorException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify webhook signature
     *
     * @param string $payload Raw request body
     * @param string $signature Stripe signature header
     * @param string $webhookSecret Webhook endpoint secret
     * @return bool
     */
    public function verifyWebhookSignature(string $payload, string $signature, string $webhookSecret): bool
    {
        try {
            \Stripe\Webhook::constructEvent($payload, $signature, $webhookSecret);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Construct webhook event
     *
     * @param string $payload
     * @param string $signature
     * @param string $webhookSecret
     * @return \stdClass|null
     */
    public function constructWebhookEvent(string $payload, string $signature, string $webhookSecret)
    {
        try {
            return \Stripe\Webhook::constructEvent($payload, $signature, $webhookSecret);
        } catch (\Exception $e) {
            return null;
        }
    }
}
