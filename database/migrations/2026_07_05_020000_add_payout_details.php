<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payouts', function (Blueprint $table) {
            $table->string('beneficiary_name')->nullable()->after('currency');
            $table->string('beneficiary_account')->nullable()->after('beneficiary_name');
            $table->string('beneficiary_ifsc')->nullable()->after('beneficiary_account');
            $table->string('razorpay_contact_id')->nullable()->after('razorpay_payout_id');
            $table->string('razorpay_fund_account_id')->nullable()->after('razorpay_contact_id');
        });
    }

    public function down()
    {
        Schema::table('payouts', function (Blueprint $table) {
            $table->dropColumn(['beneficiary_name', 'beneficiary_account', 'beneficiary_ifsc', 'razorpay_contact_id', 'razorpay_fund_account_id']);
        });
    }
};
