<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('stripe_payment_intent_id')->nullable()->after('razorpay_payment_id');
            $table->string('stripe_payment_id')->nullable()->after('stripe_payment_intent_id');
            $table->string('payment_type')->default('razorpay')->comment('razorpay or stripe')->after('payment_method');
            $table->index('stripe_payment_intent_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['stripe_payment_intent_id']);
            $table->dropColumn(['stripe_payment_intent_id', 'stripe_payment_id', 'payment_type']);
        });
    }
};
