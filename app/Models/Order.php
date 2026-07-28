<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'amount',
        'payment_method',
        'payment_type',
        'razorpay_order_id',
        'razorpay_payment_id',
        'stripe_payment_intent_id',
        'stripe_payment_id',
        'status',
        'paid_at'
    ];

    protected $dates = ['paid_at'];
}
