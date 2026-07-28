# Stripe Dashboard: What To Add

## ✅ Your Webhook Details (Ready to Configure)

**Endpoint URL:**
```
https://yourdomain.com/api/stripe/webhook
```

**Events to Subscribe:**
```
✓ payment_intent.succeeded
✓ payment_intent.payment_failed
✓ charge.dispute.created
```

---

## 📋 Step-by-Step Screenshots (Stripe Dashboard)

### Step 1: Login & Navigate
```
1. Go to: https://dashboard.stripe.com
2. Login with your Stripe account
3. Click "Developers" (top right menu)
4. Click "Webhooks" (left sidebar)
```

### Step 2: Add Endpoint
```
You'll see:
┌─────────────────────────────────┐
│ Endpoints                       │
├─────────────────────────────────┤
│ + Add Endpoint                  │ ← CLICK HERE
└─────────────────────────────────┘
```

### Step 3: Fill Out Form
```
┌─────────────────────────────────────────────┐
│ Endpoint details                            │
├─────────────────────────────────────────────┤
│                                             │
│ Endpoint URL:                               │
│ [https://yourdomain.com/api/stripe/webhook]│
│                                             │
│ Events to send:  [Select events ▼]         │ ← CLICK TO SELECT
│                                             │
│ [Add endpoint]                              │
│                                             │
└─────────────────────────────────────────────┘
```

### Step 4: Select Events
```
When you click "Select events", you'll see a search box.

Type: "payment_intent"

Results will show:
☐ payment_intent.amount_capitalizable
☐ payment_intent.canceled
✓ payment_intent.payment_failed      ← CHECK
✓ payment_intent.succeeded           ← CHECK
☐ payment_intent.created
... (more events)

Type: "charge"

Results will show:
☐ charge.captured
✓ charge.dispute.created             ← CHECK
... (more events)
```

### Step 5: Create Endpoint
```
After selecting events, click:
[Add endpoint]
```

### Step 6: Get Signing Secret
```
After creating, you'll see your endpoint in the list:

Your Endpoints
├─ https://yourdomain.com/api/stripe/webhook
│  ├─ Status: Active ✓
│  ├─ Version: Latest
│  ├─ Signing secret: whsec_live_xxxxx... [Reveal with 🔑]
│  └─ ...

Click the endpoint, scroll down, click [Reveal] next to signing secret
Copy the full secret: whsec_live_xxxxxxxxxx
```

### Step 7: Add to .env
```bash
# Update your .env file:
STRIPE_WEBHOOK_SECRET=whsec_live_xxxxxxxxxx

# Then in terminal:
php artisan config:clear
```

### Step 8: Restart App & Test
```bash
# Restart Laravel
php artisan serve

# Make a test payment at:
# http://localhost:8000/stripe-products

# Database should update automatically!
```

---

## 🔍 What Happens

### Before Webhook Configuration
```
User clicks "Buy Now"
    ↓
Payment to Stripe succeeds
    ↓
Frontend thinks it's done
    ↓
❌ Database NOT updated
❌ No payment record stored
```

### After Webhook Configuration
```
User clicks "Buy Now"
    ↓
Payment to Stripe succeeds
    ↓
Stripe sends webhook to /api/stripe/webhook
    ↓
✅ Signature verified
✅ orders table updated (status = PAID)
✅ stripe_products table updated (complete data)
✅ Logs recorded
    ↓
Frontend polls and detects change
    ↓
✅ Redirect to success page
```

---

## 🧪 Testing After Setup

### Local Testing (Recommended)
```bash
# Terminal 1: Start app
php artisan serve

# Terminal 2: Forward webhooks (Stripe CLI)
stripe listen --forward-to localhost:8000/api/stripe/webhook

# Then make payment using test card: 4242 4242 4242 4242
# Database updates immediately!
```

### Check Database
```bash
php artisan tinker
>>> StripePayment::latest()->first()

# Should show:
{
  "id": 1,
  "status": "succeeded",
  "amount": "19.99",
  "paid_at": "2026-07-28 14:30:45",
  ...
}
```

---

## ⚠️ Common Issues

### Issue: "Invalid signature" in logs
```
Cause: STRIPE_WEBHOOK_SECRET mismatch
Solution:
1. Copy fresh secret from Stripe Dashboard
2. Update .env
3. Run: php artisan config:clear
4. Restart app
```

### Issue: Webhook not received
```
Cause: URL not accessible or events not selected
Solution:
1. Verify endpoint URL is correct
2. Make sure events are checked
3. Check that HTTPS works (production)
4. In Dashboard → Webhooks → Click endpoint → Check "Recent Events"
```

### Issue: Database not updating
```
Cause: Webhook event received but not processed
Solution:
1. Check logs: tail -f storage/logs/laravel.log | grep Stripe
2. Manually retry webhook in Dashboard: Click endpoint → Click event → [Resend]
```

---

## 📊 Dashboard Event Tracking

After payments, you can see:

1. Dashboard → Developers → Webhooks
2. Click your endpoint
3. Scroll to "Recent Events"
4. Click any event to see:
   - Request sent to your endpoint
   - Response from your app
   - Timestamp
   - Whether it succeeded

---

## ✓ Checklist

Setup Checklist:
- [ ] Logged into https://dashboard.stripe.com
- [ ] Navigated to Developers → Webhooks
- [ ] Clicked "Add endpoint"
- [ ] Entered URL: `https://yourdomain.com/api/stripe/webhook`
- [ ] Selected events:
  - [ ] `payment_intent.succeeded`
  - [ ] `payment_intent.payment_failed`
  - [ ] `charge.dispute.created`
- [ ] Clicked "Add endpoint"
- [ ] Clicked endpoint and revealed signing secret
- [ ] Copied secret to .env: `STRIPE_WEBHOOK_SECRET=whsec_...`
- [ ] Ran: `php artisan config:clear`
- [ ] Restarted app
- [ ] Made test payment
- [ ] Verified database updated

**Once all checked: Webhook is fully configured! 🎉**

---

## Quick Reference

| What | Where |
|------|-------|
| **Webhook URL** | https://yourdomain.com/api/stripe/webhook |
| **Webhook Handler** | /app/Http/Controllers/Api/StripeWebhookController.php |
| **Data Storage** | stripe_products table, orders table |
| **Logs** | storage/logs/laravel.log |
| **Events Handled** | payment_intent.succeeded, payment_intent.payment_failed, charge.dispute.created |
| **Dashboard Link** | https://dashboard.stripe.com/webhooks |
