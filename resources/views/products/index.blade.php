@extends('layouts.app')

@section('title', 'Products')

@section('content')
    <h2>Products</h2>

    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="background:#f5f5f5; text-align:left;">
                <th style="padding:8px; border:1px solid #e6e6e6; width:140px;">Image</th>
                <th style="padding:8px; border:1px solid #e6e6e6;">Name</th>
                <th style="padding:8px; border:1px solid #e6e6e6;">Description</th>
                <th style="padding:8px; border:1px solid #e6e6e6; width:120px;">Price</th>
                <th style="padding:8px; border:1px solid #e6e6e6; width:120px;">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $p)
                <tr>
                    <td style="padding:8px; border:1px solid #e6e6e6;"><a href="{{$p->image}}" target="_blank"><img src="{{ $p->image }}" alt="{{ $p->product_name }}" style="max-width:120px; height:auto; display:block;"></a></td>
                    <td style="padding:8px; border:1px solid #e6e6e6; vertical-align:top;">{{ $p->product_name }}</td>
                    <td style="padding:8px; border:1px solid #e6e6e6; vertical-align:top;">{{ $p->description }}</td>
                    <td style="padding:8px; border:1px solid #e6e6e6; vertical-align:top;">${{ number_format($p->price, 2) }}</td>
                    <td style="padding:8px; border:1px solid #e6e6e6; vertical-align:top;">
                        @auth
                            <form method="POST" action="{{ route('orders.create') }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $p->id }}">
                                <button type="submit">Buy Now</button>
                            </form>
                        @else
                            <a href="{{ route('login') }}">Login to buy</a>
                        @endauth
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

@endsection
