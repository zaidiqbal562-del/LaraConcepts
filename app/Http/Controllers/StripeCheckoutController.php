<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ProductStripe;
use App\Services\StripeService;
use Illuminate\Http\Request;

class StripeCheckoutController extends Controller
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Show Stripe checkout page
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:product_stripe,id',
        ]);

        $product = ProductStripe::findOrFail($request->product_id);

        // Create local order record with PENDING status
        $order = Order::create([
            'user_id' => auth()->id() ?? null,
            'product_id' => $product->id,
            'amount' => $product->price,
            'status' => 'PENDING',
            'payment_type' => 'stripe',
        ]);

        // Create Stripe payment intent
        $result = $this->stripeService->createPaymentIntent(
            $product->price,
            'usd',
            [
                'order_id' => $order->id,
                'product_name' => $product->name,
            ]
        );

        if (!$result['success']) {
            $order->update(['status' => 'FAILED']);
            return redirect()->back()->with('error', 'Failed to create payment: ' . $result['error']);
        }

        // Save payment intent ID to order
        $order->update([
            'stripe_payment_intent_id' => $result['payment_intent_id'],
        ]);

        // Return checkout page with client secret
        return view('payments.stripe_checkout', [
            'product' => $product,
            'order' => $order,
            'client_secret' => $result['client_secret'],
            'publishable_key' => env('STRIPE_KEY'),
        ]);
    }

    /**
     * Check payment status - frontend polls this to verify webhook has processed
     */
    public function checkStatus(Order $order)
    {
        // Verify order belongs to current user if authenticated
        if (auth()->check() && $order->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Latest truth is in the database (updated by webhook only)
        return response()->json([
            'status' => $order->status,
            'paid_at' => $order->paid_at,
            'message' => match ($order->status) {
                'PAID' => 'Payment successful! Thank you for your purchase.',
                'FAILED' => 'Payment failed. Please try again.',
                'PENDING' => 'Payment pending. Please wait...',
                'DISPUTED' => 'Payment is under dispute.',
                default => 'Unknown status',
            },
        ]);
    }
}
