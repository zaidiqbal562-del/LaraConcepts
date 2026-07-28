@extends('layouts.app')

@section('title', 'Stripe Checkout')

@section('content')
    <div style="max-width:600px; margin:0 auto; padding:20px;">
        <h1>Stripe Checkout</h1>
        <div style="border:1px solid #e5e7eb; border-radius:12px; padding:20px; background:white; margin:20px 0;">
            <h2 style="font-size:18px; margin:0 0 10px;">{{ $product->name }}</h2>
            <p style="margin:0 0 16px; color:#6b7280; font-size:14px;">Order #{{ $order->id }}</p>
            <div style="background:#f3f4f6; padding:16px; border-radius:8px; margin:20px 0;">
                <div style="display:flex; justify-content:space-between; margin:0 0 10px;">
                    <span>Product:</span>
                    <strong>{{ $product->name }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between; border-top:1px solid #e5e7eb; padding-top:10px;">
                    <span style="font-size:16px;">Total:</span>
                    <strong style="font-size:16px;">{{ '$' . number_format($product->price, 2) }}</strong>
                </div>
            </div>
        </div>

        <div id="card-element" style="border:1px solid #ced4da; border-radius:4px; padding:12px; margin:20px 0;"></div>
        <div id="card-errors" style="color:#dc2626; margin:10px 0;"></div>

        <button id="card-button" style="width:100%; padding:12px; background:#6366f1; color:white; border:none; border-radius:8px; font-size:16px; cursor:pointer; margin-top:20px;">
            Pay ${{ number_format($product->price, 2) }}
        </button>

        <p style="text-align:center; margin-top:16px; color:#6b7280; font-size:14px;">
            Payment status will update automatically. Do not close this page.
        </p>
    </div>

    <script src="https://js.stripe.com/v3/"></script>
    <script>
        const stripe = Stripe('{{ $publishable_key }}');
        const elements = stripe.elements();
        const cardElement = elements.create('card');
        cardElement.mount('#card-element');

        const cardButton = document.getElementById('card-button');
        const cardErrors = document.getElementById('card-errors');

        cardButton.addEventListener('click', async (e) => {
            e.preventDefault();
            cardButton.disabled = true;
            cardButton.textContent = 'Processing...';

            // Confirm payment with Stripe
            const { error, paymentIntent } = await stripe.confirmCardPayment('{{ $client_secret }}', {
                payment_method: {
                    card: cardElement,
                    billing_details: {
                        name: 'Customer'
                    }
                }
            });

            if (error) {
                cardErrors.textContent = error.message;
                cardButton.disabled = false;
                cardButton.textContent = 'Pay ${{ number_format($product->price, 2) }}';
            } else {
                // Payment succeeded or requires further action
                if (paymentIntent.status === 'succeeded') {
                    // Poll for webhook confirmation
                    pollPaymentStatus();
                } else if (paymentIntent.status === 'requires_action') {
                    cardErrors.textContent = 'Payment requires additional authentication.';
                    cardButton.disabled = false;
                    cardButton.textContent = 'Pay ${{ number_format($product->price, 2) }}';
                } else {
                    // Poll for webhook confirmation even if not immediately succeeded
                    pollPaymentStatus();
                }
            }
        });

        function pollPaymentStatus() {
            const maxAttempts = 30; // 30 seconds
            let attempts = 0;

            const interval = setInterval(async () => {
                attempts++;

                const response = await fetch('{{ route("stripe.check-status", $order->id) }}', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    }
                });

                const data = await response.json();

                if (data.status === 'PAID') {
                    clearInterval(interval);
                    cardButton.disabled = true;
                    cardButton.textContent = 'Payment Successful!';
                    cardErrors.textContent = data.message;
                    cardErrors.style.color = '#16a34a';
                    setTimeout(() => {
                        window.location.href = '{{ route("stripe.products.index") }}';
                    }, 2000);
                } else if (data.status === 'FAILED') {
                    clearInterval(interval);
                    cardButton.disabled = false;
                    cardButton.textContent = 'Pay ${{ number_format($product->price, 2) }}';
                    cardErrors.textContent = data.message;
                } else if (attempts >= maxAttempts) {
                    clearInterval(interval);
                    cardButton.disabled = false;
                    cardButton.textContent = 'Pay ${{ number_format($product->price, 2) }}';
                    cardErrors.textContent = 'Payment confirmation timeout. Please refresh to check status.';
                }
            }, 1000); // Poll every second
        }
    </script>
@endsection
