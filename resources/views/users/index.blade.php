@extends('layouts.app')

@section('title', 'Users')

@section('content')
    <h2>Users</h2>

    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="background:#f5f5f5; text-align:left;">
                <th style="padding:8px; border:1px solid #e6e6e6;">Name</th>
                <th style="padding:8px; border:1px solid #e6e6e6;">Email</th>
                <th style="padding:8px; border:1px solid #e6e6e6; width:160px;">Payout</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td style="padding:8px; border:1px solid #e6e6e6;">{{ $user->name }}</td>
                    <td style="padding:8px; border:1px solid #e6e6e6;">{{ $user->email }}</td>
                    <td style="padding:8px; border:1px solid #e6e6e6; vertical-align:middle;">
                        <form method="POST" action="{{ route('payouts.store') }}" style="display:flex; flex-wrap:wrap; gap:6px; align-items:center;">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                            <input name="beneficiary_name" placeholder="Name" style="width:120px; padding:6px;" required>
                            <input name="beneficiary_account" placeholder="Account" style="width:120px; padding:6px;" required>
                            <input name="beneficiary_ifsc" placeholder="IFSC" style="width:90px; padding:6px;" required>
                            <input name="amount" placeholder="Amount" style="width:80px; padding:6px;" required>
                            <button type="submit">Payout</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

@endsection
