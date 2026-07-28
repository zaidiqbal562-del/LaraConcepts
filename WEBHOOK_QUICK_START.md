# ⚡ QUICK: Stripe Webhook Setup

## What You Need to Add in Stripe Dashboard

### 1. Your Webhook URL
```
https://yourdomain.com/api/stripe/webhook
```
*(Local: http://127.0.0.1:8000/api/stripe/webhook)*

### 2. Events to Subscribe
- ✅ `payment_intent.succeeded`
- ✅ `payment_intent.payment_failed`  
- ✅ `charge.dispute.created`

### 3. Copy the Signing Secret
After creating the webhook, copy: `whsec_live_xxxxx`

### 4. Update .env
```env
STRIPE_WEBHOOK_SECRET=whsec_live_xxxxx
```

### 5. Done! ✓
Payments now update database via webhook

---

## For LOCAL TESTING (Recommended First)

```bash
# Install Stripe CLI once
brew install stripe/stripe-cli/stripe  # macOS
# or visit: https://github.com/stripe/stripe-cli/releases

# Login
stripe login

# Forward webhooks (run in terminal)
stripe listen --forward-to localhost:8000/api/stripe/webhook

# Copy the secret and add to .env:
STRIPE_WEBHOOK_SECRET=whsec_test_xxxxx

# Make a test payment at /stripe-products
# Database should update automatically!
```

---

## Step-by-Step in Stripe Dashboard

1. Go to: https://dashboard.stripe.com
2. Click **Developers** (top menu)
3. Click **Webhooks** (left sidebar)
4. Click **Add endpoint**
5. Paste URL: `https://yourdomain.com/api/stripe/webhook`
6. Click **Select events**
7. Search & check:
   - [ ] `payment_intent.succeeded`
   - [ ] `payment_intent.payment_failed`
   - [ ] `charge.dispute.created`
8. Click **Add endpoint**
9. Scroll down → Click endpoint → **Reveal signing secret**
10. Copy secret → Add to .env → **STRIPE_WEBHOOK_SECRET=whsec_xxx**
11. Restart app → Done!

---

## Test It

**1. Start Laravel:**
```bash
php artisan serve
```

**2. Start Stripe CLI (if local testing):**
```bash
stripe listen --forward-to localhost:8000/api/stripe/webhook
```

**3. Make test payment:**
- Go to: `http://localhost:8000/stripe-products`
- Click "Buy Now"
- Card: `4242 4242 4242 4242`
- Expiry: `12/25`
- CVC: `123`

**4. Check database:**
```bash
php artisan tinker
>>> StripePayment::latest()->first()
# Should show: status = 'succeeded', paid_at = timestamp
```

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| "Invalid signature" | Copy fresh secret from Dashboard, update .env, restart |
| Database not updating | Check logs: `tail -f storage/logs/laravel.log \| grep Stripe` |
| Webhook not received | Make sure .env has correct secret, app restarted |
| Test payment incomplete | Check payments at: `/stripe/payments` |

---

## Your Webhook Path
```
POST /api/stripe/webhook
```
This receives Stripe webhook events and:
- ✅ Verifies signature
- ✅ Updates orders table
- ✅ Stores full payment details in stripe_products
- ✅ Logs everything
