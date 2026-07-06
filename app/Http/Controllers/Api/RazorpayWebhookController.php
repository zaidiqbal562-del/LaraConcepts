<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Payout;
use App\Services\RazorpayXService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RazorpayWebhookController extends Controller
{
    // webhook does not use auth; verify signature
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature') ?: $request->header('x-razorpay-signature');
        $service = new RazorpayXService();

        if (! $service->verifyWebhookSignature($payload, $signature)) {
            return response('invalid signature', 400);
        }

        $event = $request->input('event');
        $payloadObj = $request->input('payload', []);

        $this->processEvent($event, $payloadObj);

        return response('ok');
    }

    public function processEvent(string $event, array $payloadObj): void
    {
        if ($event === 'payment.captured' || $event === 'payment.authorized') {
            $payment = data_get($payloadObj, 'payment.entity', []);
            $razorpayOrderId = $payment['order_id'] ?? null;
            $razorpayPaymentId = $payment['id'] ?? null;

            if ($razorpayOrderId) {
                $order = Order::where('razorpay_order_id', $razorpayOrderId)->first();
                if ($order) {
                    $order->update([
                        'razorpay_payment_id' => $razorpayPaymentId,
                        'status' => 'PAID',
                        'paid_at' => now(),
                    ]);
                }
            }
        }

        if ($event === 'payment.failed') {
            $payment = data_get($payloadObj, 'payment.entity', []);
            $razorpayOrderId = $payment['order_id'] ?? null;
            if ($razorpayOrderId) {
                $order = Order::where('razorpay_order_id', $razorpayOrderId)->first();
                if ($order) {
                    $order->update(['status' => 'FAILED']);
                }
            }
        }

        if (in_array($event, ['payout.created', 'payout.processed', 'payout.failed'])) {
            $payoutData = data_get($payloadObj, 'payout.entity', []);
            $razorpayPayoutId = $payoutData['id'] ?? null;
            $status = $payoutData['status'] ?? null;

            if ($razorpayPayoutId) {
                $localPayout = Payout::where('razorpay_payout_id', $razorpayPayoutId)->first();
                if ($localPayout) {
                    if ($status === 'processed') {
                        $localPayout->update(['status' => 'PAID', 'processed_at' => now()]);
                    } elseif ($status === 'failed') {
                        $localPayout->update(['status' => 'FAILED']);
                    } else {
                        $localPayout->update(['status' => strtoupper($status ?? 'PROCESSING')]);
                    }
                }
            }
        }
    }
}
