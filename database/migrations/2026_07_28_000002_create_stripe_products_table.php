<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stripe_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            
            // Stripe identifiers
            $table->string('stripe_payment_intent_id')->unique();
            $table->string('stripe_charge_id')->nullable()->unique();
            $table->string('stripe_customer_id')->nullable();
            
            // Payment details
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('usd');
            $table->string('status')->default('pending'); // pending, succeeded, failed, canceled
            $table->string('receipt_email')->nullable();
            $table->text('receipt_url')->nullable();
            
            // Description and metadata
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->json('payment_method_details')->nullable();
            
            // Timestamps
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_message')->nullable();
            $table->timestamps();
            
            // Indexes for quick lookups
            $table->index('user_id');
            $table->index('order_id');
            $table->index('product_id');
            $table->index('stripe_payment_intent_id');
            $table->index('stripe_customer_id');
            $table->index('status');
            $table->index('paid_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stripe_products');
    }
};
