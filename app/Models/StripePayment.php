<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StripePayment extends Model
{
    protected $table = 'stripe_products';

    protected $fillable = [
        'user_id',
        'order_id',
        'product_id',
        'stripe_payment_intent_id',
        'stripe_charge_id',
        'stripe_customer_id',
        'amount',
        'currency',
        'status',
        'receipt_email',
        'receipt_url',
        'description',
        'metadata',
        'payment_method_details',
        'paid_at',
        'failed_at',
        'failure_message',
    ];

    protected $casts = [
        'metadata' => 'json',
        'payment_method_details' => 'json',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Relationship: belongs to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: belongs to Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relationship: belongs to Product
     */
    public function product()
    {
        return $this->belongsTo(ProductStripe::class, 'product_id');
    }

    /**
     * Scope: Get successful payments
     */
    public function scopeSucceeded($query)
    {
        return $query->where('status', 'succeeded');
    }

    /**
     * Scope: Get failed payments
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope: Get payments for a user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
