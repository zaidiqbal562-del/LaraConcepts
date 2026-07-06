@extends('layouts.app')

@section('title', 'Payouts')

@section('content')
    <h2>Payouts</h2>

    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="background:#f5f5f5; text-align:left;">
                <th style="padding:8px; border:1px solid #e6e6e6;">User</th>
                <th style="padding:8px; border:1px solid #e6e6e6;">Amount</th>
                <th style="padding:8px; border:1px solid #e6e6e6;">Beneficiary</th>
                <th style="padding:8px; border:1px solid #e6e6e6;">Status</th>
                <th style="padding:8px; border:1px solid #e6e6e6;">Payout ID</th>
                <th style="padding:8px; border:1px solid #e6e6e6;">Contact ID</th>
                <th style="padding:8px; border:1px solid #e6e6e6;">Fund Account ID</th>
                <th style="padding:8px; border:1px solid #e6e6e6;">Processed At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payouts as $p)
                <tr>
                    <td style="padding:8px; border:1px solid #e6e6e6;">{{ $p->user->name }} ({{ $p->user->email }})</td>
                    <td style="padding:8px; border:1px solid #e6e6e6;">₹{{ number_format($p->amount,2) }}</td>
                    <td style="padding:8px; border:1px solid #e6e6e6;">{{ $p->beneficiary_name }}<br>{{ $p->beneficiary_account }}<br>{{ $p->beneficiary_ifsc }}</td>
                    <td style="padding:8px; border:1px solid #e6e6e6;">{{ $p->status }}</td>
                    <td style="padding:8px; border:1px solid #e6e6e6;">{{ $p->razorpay_payout_id }}</td>
                    <td style="padding:8px; border:1px solid #e6e6e6;">{{ $p->razorpay_contact_id }}</td>
                    <td style="padding:8px; border:1px solid #e6e6e6;">{{ $p->razorpay_fund_account_id }}</td>
                    <td style="padding:8px; border:1px solid #e6e6e6;">{{ $p->processed_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

@endsection
