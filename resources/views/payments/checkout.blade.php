@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
    <h2>Checkout — {{ $product->product_name }}</h2>

    <p>Amount: ₹{{ number_format($order->amount, 2) }}</p>

    <button id="rzp-button">Pay with Razorpay</button>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        (function(){
            const options = {
                key: "{{ $razorpay_key_id }}",
                amount: {{ $amount }}, // paise
                currency: 'INR',
                name: "{{ config('app.name', 'App') }}",
                description: "Purchase: {{ $product->product_name }}",
                order_id: "{{ $razorpay_order_id }}",
                handler: function (response){
                    // send to backend for verification
                    fetch("{{ route('payments.verify') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            razorpay_order_id: response.razorpay_order_id,
                            razorpay_payment_id: response.razorpay_payment_id,
                            razorpay_signature: response.razorpay_signature,
                        })
                    }).then(r => r.json()).then(data => {
                        if (data.ok) {
                            alert('Payment verified — thank you!');
                            window.location = '/products';
                        } else {
                            alert('Payment verification failed: ' + (data.message || '')); 
                        }
                    }).catch(e => {
                        console.error(e);
                        alert('Verification request failed');
                    });
                },
                prefill: {},
                theme: {color: '#528FF0'}
            };

            const rzp = new Razorpay(options);
            document.getElementById('rzp-button').addEventListener('click', function(e){
                rzp.open();
                e.preventDefault();
            });
        })();
    </script>

@endsection
