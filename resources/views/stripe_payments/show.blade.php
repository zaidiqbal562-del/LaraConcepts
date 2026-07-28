@extends('layouts.app')

@section('title', 'Payment Details')

@section('content')
    <div style="padding:20px; max-width:900px; margin:0 auto;">
        <a href="{{ route('stripe.payments.index') }}" style="color:#6366f1; text-decoration:none; margin-bottom:20px; display:inline-block;">← Back to Payments</a>

        <h1>Payment Details</h1>

        <div style="display:grid; gap:20px; margin-top:20px;">
            <!-- Main Info Card -->
            <div style="border:1px solid #e5e7eb; border-radius:12px; padding:20px; background:white;">
                <h2 style="margin-top:0; font-size:18px;">Payment Information</h2>
                
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-top:16px;">
                    <div>
                        <p style="margin:0; color:#6b7280; font-size:12px; text-transform:uppercase;">Amount</p>
                        <p style="margin:8px 0 0; font-size:20px; font-weight:bold;">${{ number_format($payment->amount, 2) }} {{ strtoupper($payment->currency) }}</p>
                    </div>
                    <div>
                        <p style="margin:0; color:#6b7280; font-size:12px; text-transform:uppercase;">Status</p>
                        <p style="margin:8px 0 0;">
                            <span style="
                                padding:6px 12px;
                                border-radius:6px;
                                font-size:14px;
                                font-weight:bold;
                                @if($payment->status === 'succeeded')
                                    background:#dcfce7; color:#166534;
                                @elseif($payment->status === 'failed')
                                    background:#fee2e2; color:#991b1b;
                                @else
                                    background:#fef3c7; color:#92400e;
                                @endif
                            ">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </p>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-top:16px; border-top:1px solid #e5e7eb; padding-top:16px;">
                    <div>
                        <p style="margin:0; color:#6b7280; font-size:12px; text-transform:uppercase;">Payment Intent ID</p>
                        <p style="margin:8px 0 0; font-family:monospace; font-size:13px; word-break:break-all;">{{ $payment->stripe_payment_intent_id }}</p>
                    </div>
                    <div>
                        <p style="margin:0; color:#6b7280; font-size:12px; text-transform:uppercase;">Charge ID</p>
                        <p style="margin:8px 0 0; font-family:monospace; font-size:13px;">{{ $payment->stripe_charge_id ?? 'N/A' }}</p>
                    </div>
                </div>

                @if($payment->receipt_url)
                    <div style="margin-top:16px; padding-top:16px; border-top:1px solid #e5e7eb;">
                        <p style="margin:0 0 8px; color:#6b7280; font-size:12px; text-transform:uppercase;">Receipt</p>
                        <a href="{{ $payment->receipt_url }}" target="_blank" style="color:#6366f1; text-decoration:none;">
                            📄 View Receipt
                        </a>
                    </div>
                @endif
            </div>

            <!-- Timeline -->
            <div style="border:1px solid #e5e7eb; border-radius:12px; padding:20px; background:white;">
                <h2 style="margin-top:0; font-size:18px;">Timeline</h2>
                
                <div style="margin-top:16px;">
                    <div style="display:flex; gap:12px;">
                        <div style="min-width:100px; text-align:right; color:#6b7280; font-size:12px;">
                            Created:
                        </div>
                        <div style="color:#374151;">
                            {{ $payment->created_at->format('M d, Y H:i:s') }}
                        </div>
                    </div>

                    @if($payment->paid_at)
                        <div style="display:flex; gap:12px; margin-top:12px;">
                            <div style="min-width:100px; text-align:right; color:#16a34a; font-size:12px; font-weight:bold;">
                                Paid:
                            </div>
                            <div style="color:#374151;">
                                {{ $payment->paid_at->format('M d, Y H:i:s') }}
                            </div>
                        </div>
                    @endif

                    @if($payment->failed_at)
                        <div style="display:flex; gap:12px; margin-top:12px;">
                            <div style="min-width:100px; text-align:right; color:#dc2626; font-size:12px; font-weight:bold;">
                                Failed:
                            </div>
                            <div style="color:#374151;">
                                {{ $payment->failed_at->format('M d, Y H:i:s') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Additional Details -->
            <div style="border:1px solid #e5e7eb; border-radius:12px; padding:20px; background:white;">
                <h2 style="margin-top:0; font-size:18px;">Additional Details</h2>
                
                <div style="margin-top:16px; background:#f9fafb; padding:12px; border-radius:8px;">
                    @if($payment->receipt_email)
                        <p style="margin:0 0 8px;"><strong>Receipt Email:</strong> {{ $payment->receipt_email }}</p>
                    @endif

                    @if($payment->stripe_customer_id)
                        <p style="margin:0 0 8px;"><strong>Customer ID:</strong> <code style="background:white; padding:2px 6px; border-radius:4px;">{{ $payment->stripe_customer_id }}</code></p>
                    @endif

                    @if($payment->description)
                        <p style="margin:0 0 8px;"><strong>Description:</strong> {{ $payment->description }}</p>
                    @endif

                    @if($payment->failure_message)
                        <p style="margin:0 0 8px; color:#dc2626;"><strong>Failure Reason:</strong> {{ $payment->failure_message }}</p>
                    @endif

                    @if($payment->metadata)
                        <p style="margin:0;"><strong>Metadata:</strong></p>
                        <pre style="background:white; padding:8px; margin:8px 0 0; border-radius:4px; font-size:12px; overflow-x:auto;">{{ json_encode($payment->metadata, JSON_PRETTY_PRINT) }}</pre>
                    @endif
                </div>
            </div>

            <!-- Raw Response -->
            @if($payment->payment_method_details)
                <div style="border:1px solid #e5e7eb; border-radius:12px; padding:20px; background:white;">
                    <h2 style="margin-top:0; font-size:18px;">Payment Method Details</h2>
                    
                    <pre style="background:#f9fafb; padding:12px; border-radius:8px; font-size:12px; overflow-x:auto; margin-top:16px;">{{ json_encode($payment->payment_method_details, JSON_PRETTY_PRINT) }}</pre>
                </div>
            @endif
        </div>
    </div>
@endsection
