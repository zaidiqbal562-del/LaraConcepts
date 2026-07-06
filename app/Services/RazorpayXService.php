<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RazorpayXService
{
    protected string $keyId;
    protected string $keySecret;
    protected bool $dummy;

    public function __construct()
    {
        $this->keyId = trim(env('RAZORPAY_KEY_ID', ''));
        $this->keySecret = trim(env('RAZORPAY_KEY_SECRET', ''));
        $this->dummy = strtolower(trim(env('RAZORPAY_X_DUMMY', 'true'))) !== 'false';
    }

    public function createContact(array $data): array
    {
        if ($this->dummy) {
            $id = 'cont_test_' . uniqid();
            return [
                'success' => true,
                'id' => $id,
                'status' => 'created',
                'response' => ['id' => $id],
            ];
        }

        $resp = Http::withBasicAuth($this->keyId, $this->keySecret)
            ->post('https://api.razorpay.com/v1/contacts', $data);

        if ($resp->successful()) {
            return [
                'success' => true,
                'id' => $resp->json('id'),
                'status' => $resp->json('status', 'created'),
                'response' => $resp->json(),
            ];
        }

        return [
            'success' => false,
            'error' => $resp->json('error.description') ?? $resp->body(),
            'response' => $resp->json(),
        ];
    }

    public function createFundAccount(array $data): array
    {
        if ($this->dummy) {
            $id = 'fa_test_' . uniqid();
            return [
                'success' => true,
                'id' => $id,
                'status' => 'created',
                'response' => ['id' => $id],
            ];
        }

        $resp = Http::withBasicAuth($this->keyId, $this->keySecret)
            ->post('https://api.razorpay.com/v1/fund_accounts', $data);

        if ($resp->successful()) {
            return [
                'success' => true,
                'id' => $resp->json('id'),
                'status' => $resp->json('status', 'created'),
                'response' => $resp->json(),
            ];
        }

        return [
            'success' => false,
            'error' => $resp->json('error.description') ?? $resp->body(),
            'response' => $resp->json(),
        ];
    }

    public function createPayout(array $data): array
    {
        if ($this->dummy) {
            $id = 'payout_test_' . uniqid();
            return [
                'success' => true,
                'id' => $id,
                'status' => 'created',
                'response' => [
                    'id' => $id,
                    'status' => 'created',
                ],
            ];
        }

        $resp = Http::withBasicAuth($this->keyId, $this->keySecret)
            ->post('https://api.razorpay.com/v1/payouts', $data);

        if ($resp->successful()) {
            return [
                'success' => true,
                'id' => $resp->json('id'),
                'status' => $resp->json('status', 'created'),
                'response' => $resp->json(),
            ];
        }

        return [
            'success' => false,
            'error' => $resp->json('error.description') ?? $resp->body(),
            'response' => $resp->json(),
        ];
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $secret = trim(env('RAZORPAY_WEBHOOK_SECRET', ''));
        if (! $secret || ! $signature) {
            return false;
        }

        $expected = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expected, $signature);
    }

    public function isDummy(): bool
    {
        return $this->dummy;
    }
}
