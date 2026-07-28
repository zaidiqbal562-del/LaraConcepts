# Stripe Webhook Configuration Guide

## Your Webhook Endpoint

**Path:** `/api/stripe/webhook`  
**Method:** `POST`  
**Full URL Examples:**
- **Local Development:** `http://127.0.0.1:8000/api/stripe/webhook`
- **Production:** `https://yourdomain.com/api/stripe/webhook`

---

## Setup Instructions

### Option 1: Local Testing with Stripe CLI ⭐ (RECOMMENDED)

**Step 1: Install Stripe CLI**
```bash
# macOS
brew install stripe/stripe-cli/stripe

# Linux
curl https://files.stripe.com/stripe-cli/install.sh -O
bash install.sh

# Windows
Download from: https://github.com/stripe/stripe-cli/releases
```

**Step 2: Login to Stripe**
```bash
stripe login
```
This opens a browser to authenticate. Approve access.

**Step 3: Forward Webhooks Locally**
```bash
stripe listen --forward-to localhost:8000/api/stripe/webhook
```

**Output:**
```
> Ready! Your webhook signing secret is: whsec_test_xxxxxxxxxxxxx
```

**Step 4: Update .env**
```env
STRIPE_WEBHOOK_SECRET=whsec_test_xxxxxxxxxxxxx
```

**Step 5: That's it!**
- Your local app receives webhooks
- Webhook events show in real-time
- Database updates when payments succeed/fail

---

### Option 2: Production Setup (Stripe Dashboard)

**Step 1: Go to Stripe Dashboard**
1. Log in: https://dashboard.stripe.com
2. Navigate: **Developers** (top menu) → **Webhooks**

**Step 2: Click "Add endpoint"**

**Step 3: Enter Webhook Details**

| Field | Value |
|-------|-------|
| **Endpoint URL** | `https://yourdomain.com/api/stripe/webhook` |
| **Events** | See list below |
| **API version** | Latest (default) |

**Step 4: Select Events to Subscribe To**

Check these boxes:
- ✅ `payment_intent.succeeded`
- ✅ `payment_intent.payment_failed`
- ✅ `charge.dispute.created`

*(Your code handles these specific events)*

**Step 5: Click "Add endpoint"**

**Step 6: Copy Signing Secret**
After creating:
1. Find your endpoint in the list
2. Click to open it
3. Under "Signing secret", click "Reveal"
4. Copy the secret (starts with `whsec_live_...`)

**Step 7: Update .env**
```env
STRIPE_WEBHOOK_SECRET=whsec_live_xxxxxxxxxxxxx
```

**Step 8: Restart Application**
```bash
# If using Laravel Sail or local server
php artisan serve
```

---

## Testing the Webhook

### With Stripe CLI

```bash
# Send test event
stripe trigger payment_intent.succeeded

# Your logs should show:
# [INFO] Stripe webhook event received (type: payment_intent.succeeded)
```

### With Production Dashboard

1. Find your endpoint in Stripe Dashboard → Webhooks
2. Scroll to "Recent Events"
3. Make a test payment using test card: `4242 4242 4242 4242`
4. Check if event appears in Recent Events
5. Click event → View request/response

---

## Complete Webhook Configuration Checklist

**Local Testing with Stripe CLI:**
- [ ] Stripe CLI installed
- [ ] `stripe login` completed
- [ ] `stripe listen --forward-to localhost:8000/api/stripe/webhook` running
- [ ] `STRIPE_WEBHOOK_SECRET=whsec_test_...` in .env
- [ ] Test payment triggers webhook
- [ ] Database `stripe_products` table updated
- [ ] Check logs: `tail -f storage/logs/laravel.log`

**Production Deployment:**
- [ ] Domain HTTPS configured
- [ ] Stripe Dashboard webhook endpoint added
- [ ] Events subscribed: `payment_intent.succeeded`, `payment_intent.payment_failed`, `charge.dispute.created`
- [ ] Signing secret copied to .env
- [ ] App restarted/redeployed
- [ ] Test payment sent
- [ ] Webhook event received in Dashboard
- [ ] Database updated

---

## Webhook Events Explained

Your app handles these Stripe events:

### 1. `payment_intent.succeeded`
**When:** Payment was successful  
**Action:** Updates database
```
Order status → PAID
stripe_products.status → succeeded
stripe_products.paid_at → timestamp
```

### 2. `payment_intent.payment_failed`
**When:** Payment failed (card declined, insufficient funds, etc.)  
**Action:** Updates database
```
Order status → FAILED
stripe_products.status → failed
stripe_products.failed_at → timestamp
stripe_products.failure_message → error reason
```

### 3. `charge.dispute.created`
**When:** Customer disputes the charge (chargeback)  
**Action:** Updates database
```
Order status → DISPUTED
stripe_products.status → disputed
```

---

## File Locations

**Webhook Handler:**
- `/app/Http/Controllers/Api/StripeWebhookController.php`

**Webhook Route:**
- `/routes/api.php` → `Route::post('/stripe/webhook', ...)`

**Signature Verification:**
- `/app/Services/StripeService.php` → `verifyWebhookSignature()`

**Data Storage:**
- Database: `stripe_products` table
- Model: `/app/Models/StripePayment.php`

---

## Debugging Webhook Issues

### Check Logs
```bash
tail -f storage/logs/laravel.log | grep -i stripe
```

### View All Webhook Events (Stripe CLI)
```bash
stripe logs tail
```

### View Webhook in Dashboard
- Go to https://dashboard.stripe.com
- Developers → Webhooks
- Click your endpoint
- Scroll to "Recent Events"
- Click to view request/response

### Manual Event Retry
If webhook failed to process:
```bash
stripe events resend evt_xxxxx
```

---

## Troubleshooting

### "Invalid signature" Error
**Cause:** `STRIPE_WEBHOOK_SECRET` mismatch  
**Solution:** 
- Copy fresh secret from Dashboard
- Update .env
- Restart app

### Webhook Not Received
**Checklist:**
1. URL is correct and accessible (HTTPS in production)
2. Secret is correct
3. Events are selected in Dashboard
4. Firewall/WAF not blocking webhooks
5. Laravel logs show no errors

### Status Still Not Updating
**Check:**
```bash
# View database updates
php artisan tinker
>>> StripePayment::latest()->first()

# Check webhook logs
tail -f storage/logs/laravel.log | grep "handlePayment"
```

---

## Security Notes

✅ **Signature Verification:** Every webhook is verified with HMAC-SHA256  
✅ **No Card Data:** Your server never handles credit card info  
✅ **Idempotency:** Database updates are safe even if webhook is received twice  
✅ **Webhook Secret:** Keep `STRIPE_WEBHOOK_SECRET` private

---

## Your Current Setup Status

```
✅ App webhook endpoint: /api/stripe/webhook
✅ Webhook handler: StripeWebhookController
✅ Data storage: stripe_products table
✅ Events handled: payment_intent.succeeded, payment_intent.payment_failed, charge.dispute.created

❌ Stripe Dashboard: Needs webhook endpoint configuration
❌ STRIPE_WEBHOOK_SECRET: Needs to be updated with Dashboard secret

→ NEXT STEP: 
  1. Choose local testing (Stripe CLI) OR production setup
  2. Follow instructions above
  3. Test with payment
  4. Verify database updates
```

---

## Quick Links

- Stripe Dashboard: https://dashboard.stripe.com
- Webhook Settings: https://dashboard.stripe.com/webhooks
- Stripe CLI Docs: https://stripe.com/docs/stripe-cli
- Webhook Events: https://stripe.com/docs/webhooks
- Testing: https://stripe.com/docs/testing

---

## Example: Complete Local Setup (5 minutes)

```bash
# Terminal 1: Start Laravel server
cd /home/rcv/Desktop/LaraConcepts
php artisan serve

# Terminal 2: Start Stripe CLI webhook forwarding
stripe listen --forward-to localhost:8000/api/stripe/webhook

# Terminal 3: Watch logs
tail -f storage/logs/laravel.log

# Terminal 4: Make test payment
# Navigate to http://localhost:8000/stripe-products
# Click "Buy Now"
# Use test card: 4242 4242 4242 4242
# Watch Terminal 3 for webhook logs
# Check database for updates
```

That's it! Webhook is receiving and database is updating.
