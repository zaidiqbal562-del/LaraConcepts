# Stripe Payment Integration Guide

## Overview

This guide explains the complete Stripe payment integration for the Stripe Products feature. The key principle is: **Database status is updated ONLY by webhook responses, never by frontend callbacks.**

## Architecture

### Payment Flow

```
1. User clicks "Buy Now" on Stripe Products page
2. Frontend POST to /stripe/checkout with product_id
3. Backend creates:
   - Local Order (status: PENDING)
   - Stripe PaymentIntent
4. Frontend shows Stripe card element checkout form
5. User submits card details to Stripe (not to your backend)
6. Stripe processes payment and sends webhook to /api/stripe/webhook
7. Webhook updates order status (PAID/FAILED) - this is the SINGLE SOURCE OF TRUTH
8. Frontend polls /stripe/order/{id}/status to check if webhook has processed
9. Once PAID is confirmed, redirect user to success page
```

## Database Schema

### Orders Table Additions

The migration `2026_07_28_000001_add_stripe_fields_to_orders_table.php` added:

```sql
stripe_payment_intent_id VARCHAR  -- Stripe Payment Intent ID
stripe_payment_id VARCHAR          -- Stripe Charge/Payment ID
payment_type VARCHAR               -- 'stripe' or 'razorpay'
```

### Order Status Values

- `PENDING` - Order created, awaiting payment confirmation from webhook
- `PAID` - Payment succeeded (webhook only)
- `FAILED` - Payment failed (webhook only)
- `DISPUTED` - Charge dispute created (webhook only)

## Files Overview

### 1. Service Layer

**`app/Services/StripeService.php`**
- Creates payment intents
- Retrieves payment intent details
- Verifies webhook signatures using Stripe's security library
- Constructs webhook events

### 2. Controllers

**`app/Http/Controllers/StripeCheckoutController.php`**
- `checkout()` - Creates order and payment intent, shows checkout form
- `checkStatus()` - Returns current order status (read from DB, updated by webhook only)

**`app/Http/Controllers/Api/StripeWebhookController.php`**
- `handle()` - Main webhook handler (ONLY place where order status is updated to PAID)
- `handlePaymentIntentSucceeded()` - Updates order to PAID when webhook fires
- `handlePaymentIntentFailed()` - Updates order to FAILED when webhook fires
- `handleChargeDispute()` - Updates order to DISPUTED when dispute occurs

### 3. Views

**`resources/views/payments/stripe_checkout.blade.php`**
- Displays Stripe card element
- Handles payment submission to Stripe
- Polls `/stripe/order/{id}/status` endpoint
- Shows payment status updates

**`resources/views/stripe_products/index.blade.php`**
- Lists Stripe products from `product_stripe` table
- "Buy Now" button POSTs to `/stripe/checkout`

### 4. Routes

**Web Routes** (`routes/web.php`):
```php
POST   /stripe/checkout                     → StripeCheckoutController@checkout
GET    /stripe/order/{order}/status         → StripeCheckoutController@checkStatus
GET    /stripe-products                     → SMSController@stripeProducts
```

**API Routes** (`routes/api.php`):
```php
POST   /api/stripe/webhook                  → StripeWebhookController@handle
```

## Environment Variables

Required `.env` variables (already set):
```
STRIPE_KEY=pk_test_...                     # Publishable key (frontend)
STRIPE_SECRET=sk_test_...                  # Secret key (backend - keep private)
STRIPE_WEBHOOK_SECRET=whsec_...            # Webhook signing secret
```

## Webhook Setup Instructions

### Local Development (Testing)

To receive webhooks locally, use Stripe CLI:

```bash
# Install Stripe CLI: https://stripe.com/docs/stripe-cli

# Login to Stripe
stripe login

# Forward webhooks to your local endpoint
stripe listen --forward-to localhost:8000/api/stripe/webhook

# Get your webhook signing secret (displayed after running listen)
# Update STRIPE_WEBHOOK_SECRET in .env
```

### Production

1. Go to Stripe Dashboard → Developers → Webhooks
2. Click "Add endpoint"
3. URL: `https://yourdomain.com/api/stripe/webhook`
4. Events to send:
   - `payment_intent.succeeded`
   - `payment_intent.payment_failed`
   - `charge.dispute.created`
5. Copy the webhook signing secret to `.env`

## Security Notes

✓ **Webhook Signature Verification**: All webhooks verify the Stripe signature using `Stripe\Webhook::constructEvent()`. Unsigned/invalid webhooks are rejected.

✓ **CSRF Protection**: Checkout form uses Laravel's CSRF token.

✓ **No Direct Status Updates from Frontend**: Frontend cannot update order status. It can only:
  - Create orders/payment intents (via `/stripe/checkout`)
  - Check status (via `/stripe/order/{id}/status`)

✓ **Authorization**: `checkStatus()` verifies user owns the order (if authenticated).

✓ **No Card Storage**: Cards are handled entirely by Stripe. Your server never handles card details.

## Testing Payments

### Test Card Numbers

Stripe provides test cards. In test mode:

```
Success:  4242 4242 4242 4242
Decline:  4000 0000 0000 0002
3D Secure: 4000 0025 0000 3155
```

Expiry: Any future date
CVC: Any 3 digits

### Test Flow

1. Navigate to `/stripe-products`
2. Click "Buy Now" on any product
3. Use test card `4242 4242 4242 4242`
4. Expiry: 12/25, CVC: 123
5. Submit
6. With Stripe CLI listening, webhook fires immediately
7. Status updates in DB
8. Frontend detects status change and confirms payment

## Logging

All events are logged to `storage/logs/laravel.log`:

```
[2026-07-28] Stripe webhook event received (type: payment_intent.succeeded)
[2026-07-28] Payment intent succeeded (id: pi_...)
[2026-07-28] Order marked as PAID (order_id: 5, payment_intent_id: pi_...)
```

Check logs when debugging webhook issues.

## Common Issues

### "Invalid signature" error
- Webhook secret mismatch between `.env` and Stripe Dashboard
- If using Stripe CLI, a new secret is generated each session
- Solution: Always update `STRIPE_WEBHOOK_SECRET` when re-running Stripe CLI

### Payment shows PENDING indefinitely
- Webhook not received/processed
- Check logs for webhook errors
- Ensure webhook URL is accessible
- Verify webhook signing secret

### Frontend stuck on payment confirmation
- Frontend polling detects when DB status changes
- Default: polls every 1 second for 30 seconds
- Adjust timing in `stripe_checkout.blade.php` if needed

## Troubleshooting Webhook Delivery

1. Check endpoint receiving requests:
```bash
stripe logs tail
```

2. View specific webhook event:
```bash
stripe events list
stripe events retrieve evt_xxx
```

3. Manually replay a webhook:
```bash
stripe events resend evt_xxx
```

## Files Modified/Created

✓ `app/Services/StripeService.php` - New
✓ `app/Http/Controllers/Api/StripeWebhookController.php` - New
✓ `app/Http/Controllers/StripeCheckoutController.php` - New
✓ `app/Models/Order.php` - Updated (added payment_type, stripe fields to $fillable)
✓ `resources/views/payments/stripe_checkout.blade.php` - New
✓ `resources/views/stripe_products/index.blade.php` - Updated (linked to checkout)
✓ `routes/web.php` - Updated (added Stripe routes)
✓ `routes/api.php` - Updated (added webhook route)
✓ `database/migrations/2026_07_28_000001_add_stripe_fields_to_orders_table.php` - New
✓ `composer.json` - Updated (added stripe/stripe-php)

## Next Steps

1. Test locally with Stripe CLI
2. Create test payments with test card numbers
3. Verify orders are marked PAID via webhook
4. Deploy to production and configure webhook in Stripe Dashboard
5. Monitor logs for any errors

---

**Key Principle**: Database is the single source of truth, updated only by verified webhook events. Frontend is informational only.
