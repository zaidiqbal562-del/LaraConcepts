@extends('layouts.app')

@section('title', 'Stripe Payments')

@section('content')
    <div style="padding:20px;">
        <h1>Stripe Payments History</h1>
        
        @if($payments->isEmpty())
            <div style="padding:16px; border:1px solid #e5e7eb; background:#f9fafb; border-radius:8px; margin-top:20px;">
                <p style="margin:0; color:#6b7280;">No payments recorded yet.</p>
            </div>
        @else
            <table style="width:100%; border-collapse:collapse; margin-top:20px;">
                <thead>
                    <tr style="background:#f3f4f6; border-bottom:2px solid #e5e7eb;">
                        <th style="padding:12px; text-align:left; border-right:1px solid #e5e7eb;">ID</th>
                        <th style="padding:12px; text-align:left; border-right:1px solid #e5e7eb;">Amount</th>
                        <th style="padding:12px; text-align:left; border-right:1px solid #e5e7eb;">Status</th>
                        <th style="padding:12px; text-align:left; border-right:1px solid #e5e7eb;">User</th>
                        <th style="padding:12px; text-align:left; border-right:1px solid #e5e7eb;">Date</th>
                        <th style="padding:12px; text-align:left;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                        <tr style="border-bottom:1px solid #e5e7eb; hover:background:#f9fafb;">
                            <td style="padding:12px; border-right:1px solid #e5e7eb;"><code style="background:#f3f4f6; padding:2px 6px; border-radius:4px; font-size:12px;">{{ substr($payment->stripe_payment_intent_id, 0, 15) }}...</code></td>
                            <td style="padding:12px; border-right:1px solid #e5e7eb;"><strong>${{ number_format($payment->amount, 2) }} {{ strtoupper($payment->currency) }}</strong></td>
                            <td style="padding:12px; border-right:1px solid #e5e7eb;">
                                <span style="
                                    padding:4px 8px;
                                    border-radius:4px;
                                    font-size:12px;
                                    font-weight:bold;
                                    @if($payment->status === 'succeeded')
                                        background:#dcfce7; color:#166534;
                                    @elseif($payment->status === 'failed')
                                        background:#fee2e2; color:#991b1b;
                                    @elseif($payment->status === 'pending')
                                        background:#fef3c7; color:#92400e;
                                    @else
                                        background:#f3f4f6; color:#374151;
                                    @endif
                                ">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td style="padding:12px; border-right:1px solid #e5e7eb;">{{ $payment->user_id ?? 'Guest' }}</td>
                            <td style="padding:12px; border-right:1px solid #e5e7eb;">{{ $payment->paid_at?->format('M d, Y H:i') ?? $payment->created_at->format('M d, Y H:i') }}</td>
                            <td style="padding:12px;">
                                <a href="{{ route('stripe.payment.show', $payment->id) }}" style="color:#6366f1; text-decoration:none;">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top:20px;">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
@endsection
