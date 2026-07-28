<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RazorpayWebhookController;
use App\Http\Controllers\Api\StripeWebhookController;

// Webhook routes don't require authentication
Route::post('/razorpay/webhook', [RazorpayWebhookController::class, 'handle']);
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])->name('stripe.webhook');

// Stripe payment lookup
use App\Http\Controllers\StripePaymentController;
Route::get('/stripe/payment-by-intent', [StripePaymentController::class, 'getByIntentId']);
Route::middleware('auth')->get('/stripe/my-payments-api', [StripePaymentController::class, 'apiUserPayments']);
    //ok