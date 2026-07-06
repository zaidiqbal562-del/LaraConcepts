<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RazorpayWebhookController;

Route::post('/razorpay/webhook', [RazorpayWebhookController::class, 'handle']);
    