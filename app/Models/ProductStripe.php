<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductStripe extends Model
{
    protected $table = 'product_stripe';

    protected $fillable = [
        'name',
        'price',
    ];
}
