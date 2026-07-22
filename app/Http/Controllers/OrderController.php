<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // requires auth for creating orders
    public function __construct()
    {
        $this->middleware('auth')->only('create');
    }

    /**
     * Create a Razorpay order and local Order (status PENDING), then show checkout.
     */
    public function create(Request $request)
    {
        $request->validate(['product_id' => 'required|integer|exists:products,id']);

        $product = Product::findOrFail($request->product_id);
        $amount = (float) $product->price; // in INR

        $keyId = env('RAZORPAY_KEY_ID');
        $keySecret = env('RAZORPAY_KEY_SECRET');

        if (!$keyId || !$keySecret) {
            return back()->with('error', 'Razorpay keys not configured.');
        }

        // create local order record first
        $order = Order::create([
            'user_id' => auth()->id(),  
            'product_id' => $product->id,
            'amount' => $amount,
            'status' => 'PENDING',
        ]);

        // create razorpay order via API
        $payload = [
            'amount' => intval($amount * 100), // paise
            'currency' => 'INR',
            'receipt' => 'order_rcpt_' . $order->id,
            'payment_capture' => 1,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.razorpay.com/v1/orders');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $keyId . ':' . $keySecret);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        $resp = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($statusCode !== 200 && $statusCode !== 201) {
            // mark order failed
            $order->update(['status' => 'FAILED']);
            return back()->with('error', 'Failed to create Razorpay order.');
        }

        $data = json_decode($resp, true);
        $razorpayOrderId = $data['id'] ?? null;

        if (!$razorpayOrderId) {
            $order->update(['status' => 'FAILED']);
            return back()->with('error', 'Invalid response from Razorpay.');
        }

        // save razorpay order id
        $order->update(['razorpay_order_id' => $razorpayOrderId]);

        // show checkout page
        return view('payments.checkout', [
            'product' => $product,
            'order' => $order,
            'razorpay_order_id' => $razorpayOrderId,
            'razorpay_key_id' => $keyId,
            'amount' => intval($amount * 100),
        ]);
    }

    /**
     * Verify signature returned from frontend and update order status.
     */
   public function verify(Request $request)
{
    $request->validate([
        'razorpay_order_id'   => 'required|string',
        'razorpay_payment_id' => 'required|string',
        'razorpay_signature'  => 'required|string',
    ]);

    $keySecret = env('RAZORPAY_KEY_SECRET');

    $orderId   = $request->razorpay_order_id;
    $paymentId = $request->razorpay_payment_id;
    $signature = $request->razorpay_signature;

    // Generate expected signature
    $expectedSignature = hash_hmac(
        'sha256',
        $orderId . '|' . $paymentId,
        $keySecret
    );

    // Find local order
    $order = Order::where('razorpay_order_id', $orderId)->first();

    if (!$order) {
        return response()->json([
            'success' => false,
            'message' => 'Order not found'
        ], 404);
    }

    // Verify signature
    if (!hash_equals($expectedSignature, $signature)) {

        $order->update([
            'status' => 'FAILED'
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Invalid signature'
        ], 400);
    }

    // Save payment id only (optional but useful)
    $order->update([
        'razorpay_payment_id' => $paymentId
    ]);

    // DO NOT mark as PAID here.
    // Wait for payment.captured webhook.

    return response()->json([
        'success' => true,
        'message' => 'Payment verified. Waiting for confirmation.'
    ]);
}
}
