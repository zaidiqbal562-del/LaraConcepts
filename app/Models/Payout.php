<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Payout extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'currency',
        'status',
        'razorpay_payout_id',
        'processed_at',
        'beneficiary_name',
        'beneficiary_account',
        'beneficiary_ifsc',
        'razorpay_contact_id',
        'razorpay_fund_account_id',
    ];

    protected $dates = ['processed_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
