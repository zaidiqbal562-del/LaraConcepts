#!/usr/bin/env bash
# Send a simulated Razorpay webhook to your app (payment.captured)
# Usage: ./scripts/send_razorpay_webhook.sh [url] [webhook_secret] [razorpay_order_id] [razorpay_payment_id]
# Example: ./scripts/send_razorpay_webhook.sh http://127.0.0.1:8000/api/razorpay/webhook mysecret order_test_123 pay_test_123

URL=${1:-http://127.0.0.1:8000/api/razorpay/webhook}
SECRET=${2:-${RAZORPAY_WEBHOOK_SECRET}}
RP_ORDER_ID=${3:-order_test_123}
RP_PAYMENT_ID=${4:-pay_test_123}

if [ -z "$SECRET" ]; then
  echo "Error: webhook secret not provided. Pass as second arg or set RAZORPAY_WEBHOOK_SECRET env var."
  exit 1
fi

PAYLOAD=$(cat <<EOF
{"event":"payment.captured","payload":{"payment":{"entity":{"id":"$RP_PAYMENT_ID","order_id":"$RP_ORDER_ID","amount":999,"currency":"INR"}}}}
EOF
)

SIG=$(printf "%s" "$PAYLOAD" | openssl dgst -sha256 -hmac "$SECRET" -binary | xxd -p -c 256)

echo "POSTing to $URL"
echo "Payload: $PAYLOAD"
echo "Signature: $SIG"

curl -s -o /dev/stderr -w "\nHTTP_CODE: %{http_code}\n" -X POST "$URL" \
  -H "Content-Type: application/json" \
  -H "X-Razorpay-Signature: $SIG" \
  -d "$PAYLOAD"

exit 0
