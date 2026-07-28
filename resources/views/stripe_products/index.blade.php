@extends('layouts.app')

@section('title', 'Stripe Products')

@section('content')
    <h1>Stripe Products</h1>
    <p>Browse the Stripe products below and click Buy Now.</p>

    @if($products->isEmpty())
        <div style="padding:16px; border:1px solid #e5e7eb; background:#f9fafb; border-radius:8px;">
            <p style="margin:0; color:#6b7280;">No Stripe products are available yet. Run the migration to populate sample items.</p>
        </div>
    @else
        <div style="display:grid; gap:16px; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-top:20px;">
            @foreach($products as $product)
                <div style="border:1px solid #e5e7eb; border-radius:12px; padding:18px; background:white; box-shadow:0 1px 2px rgba(0,0,0,0.05);">
                    <h2 style="font-size:18px; margin:0 0 10px;">{{ $product->name }}</h2>
                    <p style="margin:0 0 16px; color:#4b5563;">Price: ${{ number_format($product->price, 2) }}</p>
                    <form action="{{ route('stripe.checkout') }}" method="POST" style="margin:0;">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <button type="submit" style="border:none; background:#6366f1; color:white; padding:10px 14px; border-radius:8px; cursor:pointer; width:100%;">Buy Now</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
@endsection
