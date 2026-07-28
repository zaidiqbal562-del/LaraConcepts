<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\StripePayment;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Handle Stripe webhooks
     * Only this endpoint updates order status to PAID
     */
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $webhookSecret = env('STRIPE_WEBHOOK_SECRET');

        // Verify webhook signature
        if (!$this->stripeService->verifyWebhookSignature($payload, $signature, $webhookSecret)) {
            Log::warning('Stripe webhook signature verification failed');
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        // Construct the event
        $event = $this->stripeService->constructWebhookEvent($payload, $signature, $webhookSecret);

        if (!$event) {
            Log::warning('Failed to construct Stripe webhook event');
            return response()->json(['error' => 'Failed to construct event'], 400);
        }

        $eventType = $event->type;
        Log::info('Stripe webhook event received', ['type' => $eventType]);

        switch ($eventType) {
            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event->data->object);
                break;

            case 'payment_intent.payment_failed':
                $this->handlePaymentIntentFailed($event->data->object);
                break;

            case 'charge.dispute.created':
                $this->handleChargeDispute($event->data->object);
                break;

            default:
                Log::info('Unhandled Stripe webhook event', ['type' => $eventType]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Handle successful payment intent
     */
    private function handlePaymentIntentSucceeded($paymentIntent)
    {
        Log::info('Payment intent succeeded', ['id' => $paymentIntent->id]);

        // Find order by payment intent ID
        $order = Order::where('stripe_payment_intent_id', $paymentIntent->id)->first();

        if (!$order) {
            Log::warning('Order not found for payment intent', ['payment_intent_id' => $paymentIntent->id]);
            return;
        }

        $chargeId = $paymentIntent->charges->data[0]->id ?? null;
        $chargeObject = $paymentIntent->charges->data[0] ?? null;

        // Update order status to PAID only from webhook
        if ($order->status !== 'PAID') {
            $order->update([
                'status' => 'PAID',
                'stripe_payment_id' => $chargeId,
                'paid_at' => now(),
                'payment_type' => 'stripe',
            ]);

            Log::info('Order marked as PAID', ['order_id' => $order->id, 'payment_intent_id' => $paymentIntent->id]);
        }

        // Store complete payment details in stripe_products table
        $this->storeStripePaymentDetails($paymentIntent, $chargeObject, $order, 'succeeded');
    }

    /**
     * Handle failed payment intent
     */
    private function handlePaymentIntentFailed($paymentIntent)
    {
        Log::warning('Payment intent failed', ['id' => $paymentIntent->id, 'last_error' => $paymentIntent->last_payment_error]);

        // Find order by payment intent ID
        $order = Order::where('stripe_payment_intent_id', $paymentIntent->id)->first();

        if (!$order) {
            Log::warning('Order not found for failed payment intent', ['payment_intent_id' => $paymentIntent->id]);
            return;
        }

        // Update order status to FAILED
        if ($order->status !== 'FAILED') {
            $order->update([
                'status' => 'FAILED',
                'payment_type' => 'stripe',
            ]);

            Log::info('Order marked as FAILED', ['order_id' => $order->id, 'payment_intent_id' => $paymentIntent->id]);
        }

        // Store failed payment details
        $failureMessage = $paymentIntent->last_payment_error?->message ?? 'Payment failed';
        $this->storeStripePaymentDetails($paymentIntent, null, $order, 'failed', $failureMessage);
    }

    /**
     * Handle charge disputes
     */
    private function handleChargeDispute($charge)
    {
        Log::warning('Charge dispute created', ['charge_id' => $charge->id]);

        $order = Order::where('stripe_payment_id', $charge->id)->first();

        if ($order) {
            $order->update([
                'status' => 'DISPUTED',
            ]);

            Log::warning('Order marked as DISPUTED', ['order_id' => $order->id]);
        }

        // Update stripe_products record status
        StripePayment::where('stripe_charge_id', $charge->id)->update([
            'status' => 'disputed',
        ]);
    }

    /**
     * Store complete payment details from Stripe response
     */
    private function storeStripePaymentDetails($paymentIntent, $chargeObject, Order $order, $status, $failureMessage = null)
    {
        try {
            // Extract payment method details
            $paymentMethodDetails = null;
            if ($chargeObject && isset($chargeObject->payment_method_details)) {
                $paymentMethodDetails = (array) $chargeObject->payment_method_details;
            }

            // Check if record already exists
            $stripePayment = StripePayment::where('stripe_payment_intent_id', $paymentIntent->id)->first();

            $data = [
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'product_id' => $order->product_id,
                'stripe_payment_intent_id' => $paymentIntent->id,
                'stripe_charge_id' => $chargeObject->id ?? null,
                'stripe_customer_id' => $paymentIntent->customer ?? null,
                'amount' => ($paymentIntent->amount / 100), // Convert cents to dollars
                'currency' => strtoupper($paymentIntent->currency),
                'status' => $status,
                'receipt_email' => $chargeObject->receipt_email ?? $paymentIntent->receipt_email ?? null,
                'receipt_url' => $chargeObject->receipt_url ?? null,
                'description' => $paymentIntent->description ?? null,
                'metadata' => $paymentIntent->metadata ? (array) $paymentIntent->metadata : null,
                'payment_method_details' => $paymentMethodDetails,
                'paid_at' => $status === 'succeeded' ? now() : null,
                'failed_at' => $status === 'failed' ? now() : null,
                'failure_message' => $failureMessage,
            ];

            if ($stripePayment) {
                $stripePayment->update($data);
                Log::info('StripePayment record updated', ['id' => $stripePayment->id]);
            } else {
                StripePayment::create($data);
                Log::info('StripePayment record created', ['payment_intent_id' => $paymentIntent->id]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to store Stripe payment details', [
                'payment_intent_id' => $paymentIntent->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
