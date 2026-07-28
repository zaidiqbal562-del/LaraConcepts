<?php

namespace App\Http\Controllers;

use App\Models\StripePayment;
use Illuminate\Http\Request;

class StripePaymentController extends Controller
{
    /**
     * Show all Stripe payments
     */
    public function index()
    {
        $payments = StripePayment::latest()->paginate(15);
        
        return view('stripe_payments.index', compact('payments'));
    }

    /**
     * Show a single payment details
     */
    public function show(StripePayment $payment)
    {
        // If user is authenticated, check authorization
        if (auth()->check() && $payment->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        return view('stripe_payments.show', compact('payment'));
    }

    /**
     * Show payments for current user
     */
    public function userPayments()
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $payments = StripePayment::forUser(auth()->id())
            ->latest()
            ->paginate(15);

        return view('stripe_payments.user', compact('payments'));
    }

    /**
     * API endpoint: Get payment by intent ID
     */
    public function getByIntentId(Request $request)
    {
        $request->validate(['payment_intent_id' => 'required|string']);

        $payment = StripePayment::where('stripe_payment_intent_id', $request->payment_intent_id)
            ->first();

        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        return response()->json($payment);
    }

    /**
     * API endpoint: Get payments by user
     */
    public function apiUserPayments()
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $payments = StripePayment::forUser(auth()->id())
            ->latest()
            ->get();

        return response()->json($payments);
    }
}
