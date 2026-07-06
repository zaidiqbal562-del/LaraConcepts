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
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $keySecret = env('RAZORPAY_KEY_SECRET');

        $orderId = $request->input('razorpay_order_id');
        $paymentId = $request->input('razorpay_payment_id');
        $signature = $request->input('razorpay_signature');

        // verify signature
        $expected = hash_hmac('sha256', $orderId . '|' . $paymentId, $keySecret);

        $localOrder = Order::where('razorpay_order_id', $orderId)->first();
        if (!$localOrder) {
            return response()->json(['ok' => false, 'message' => 'Order not found'], 404);
        }

        if (hash_equals($expected, $signature)) {
            $localOrder->update([
                'razorpay_payment_id' => $paymentId,
                'status' => 'PAID',
                'paid_at' => now(),
            ]);

            // Note: still wait for webhook for final reconciliation in production

            return response()->json(['ok' => true]);
        }

        $localOrder->update(['status' => 'FAILED']);
        return response()->json(['ok' => false, 'message' => 'Invalid signature'], 400);
    }
}
