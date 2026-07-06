<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use App\Models\User;
use App\Services\RazorpayXService;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $payouts = Payout::with('user')->orderBy('created_at', 'desc')->get();
        return view('payouts.index', compact('payouts'));
    }

    /**
     * Create a payout record and process it through RazorpayX (mock or real API).
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'amount' => 'required|numeric|min:1',
            'beneficiary_name' => 'required|string',
            'beneficiary_account' => 'required|string',
            'beneficiary_ifsc' => 'required|string',
        ]);

        $user = User::findOrFail($request->user_id);

        $payout = Payout::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'currency' => 'INR',
            'status' => 'PENDING',
            'beneficiary_name' => $request->beneficiary_name,
            'beneficiary_account' => $request->beneficiary_account,
            'beneficiary_ifsc' => $request->beneficiary_ifsc,
        ]);

        $service = new RazorpayXService();

        $contactPayload = [
            'name' => $request->beneficiary_name,
            'email' => $user->email,
            'contact' => $user->phone ?? null,
            'type' => 'employee',
        ];

        $contact = $service->createContact($contactPayload);
        if (! $contact['success']) {
            $payout->update(['status' => 'FAILED']);
            return back()->with('error', 'Contact creation failed: ' . ($contact['error'] ?? 'Unknown error'));
        }

        $payout->update(['razorpay_contact_id' => $contact['id']]);

        $fundAccountPayload = [
            'contact_id' => $contact['id'],
            'account_type' => 'bank_account',
            'bank_account' => [
                'name' => $request->beneficiary_name,
                'ifsc' => $request->beneficiary_ifsc,
                'account_number' => $request->beneficiary_account,
            ],
        ];

        $fundAccount = $service->createFundAccount($fundAccountPayload);
        if (! $fundAccount['success']) {
            $payout->update(['status' => 'FAILED']);
            return back()->with('error', 'Fund account creation failed: ' . ($fundAccount['error'] ?? 'Unknown error'));
        }

        $payout->update(['razorpay_fund_account_id' => $fundAccount['id']]);

        $payoutPayload = [
            'fund_account_id' => $fundAccount['id'],
            'amount' => intval($payout->amount * 100),
            'currency' => $payout->currency,
            'mode' => 'IMPS',
            'purpose' => 'payout',
            'queue_if_low_balance' => true,
            'reference_id' => 'payout_ref_' . $payout->id,
        ];

        $payoutResult = $service->createPayout($payoutPayload);
        if (! $payoutResult['success']) {
            $payout->update(['status' => 'FAILED']);
            return back()->with('error', 'Payout creation failed: ' . ($payoutResult['error'] ?? 'Unknown error'));
        }

        $payout->update([
            'status' => 'PROCESSING',
            'razorpay_payout_id' => $payoutResult['id'],
        ]);

        if ($service->isDummy()) {
            $webhookController = new \App\Http\Controllers\Api\RazorpayWebhookController();
            $webhookController->processEvent('payout.processed', [
                'payout' => [
                    'entity' => [
                        'id' => $payoutResult['id'],
                        'status' => 'processed',
                    ],
                ],
            ]);
        }

        return back()->with('success', 'Payout created and now processing.');
    }
}
