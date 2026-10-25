<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class RazorpayService
{
    public static function getKeyId(): string
    {
        return (string) (config('services.razorpay.key_id') ?: env('RAZORPAY_KEY_ID', ''));
    }

    public static function getKeySecret(): string
    {
        return (string) (config('services.razorpay.key_secret') ?: env('RAZORPAY_KEY_SECRET', ''));
    }

    public static function getWebhookSecret(): string
    {
        return (string) (config('services.razorpay.webhook_secret') ?: env('RAZORPAY_WEBHOOK_SECRET', ''));
    }

    public static function isMockMode(): bool
    {
        $key = self::getKeyId();
        $secret = self::getKeySecret();

        return empty($key) || empty($secret) || str_contains($key, 'demo') || str_starts_with($key, 'mock_') || str_contains($secret, 'demo');
    }

    /**
     * Create a Razorpay Order
     */
    public function createOrder(array $params): array
    {
        $amount = (int) ($params['amount'] ?? 0);
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Valid order amount is required (in paise)');
        }

        $currency = $params['currency'] ?? 'INR';
        $receipt = substr((string) ($params['receipt'] ?? 'rcpt_'.time()), 0, 40);
        $notes = $params['notes'] ?? [];

        if (self::isMockMode()) {
            $mockOrderId = 'order_test_'.time().'_'.Str::random(6);

            return [
                'id' => $mockOrderId,
                'entity' => 'order',
                'amount' => $amount,
                'amount_paid' => 0,
                'amount_due' => $amount,
                'currency' => $currency,
                'receipt' => $receipt,
                'status' => 'created',
                'attempts' => 0,
                'notes' => $notes,
                'created_at' => time(),
                '_is_simulated' => true,
            ];
        }

        $response = Http::withBasicAuth(self::getKeyId(), self::getKeySecret())
            ->post('https://api.razorpay.com/v1/orders', [
                'amount' => $amount,
                'currency' => $currency,
                'receipt' => $receipt,
                'notes' => $notes,
            ]);

        if (! $response->successful()) {
            $err = $response->json('error.description') ?: $response->json('error.code') ?: 'Failed to create Razorpay order';
            throw new \RuntimeException("Razorpay Order Creation Failed: {$err}");
        }

        return $response->json();
    }

    /**
     * Verify HMAC-SHA256 signature for Checkout flow
     */
    public function verifyCheckoutSignature(string $orderId, string $paymentId, string $signature): bool
    {
        if (empty($orderId) || empty($paymentId) || empty($signature)) {
            return false;
        }

        $secret = self::getKeySecret();
        if (empty($secret)) {
            return false;
        }

        $expected = hash_hmac('sha256', "{$orderId}|{$paymentId}", $secret);

        return hash_equals($expected, $signature);
    }

    /**
     * Verify Webhook signature
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        if (empty($payload) || empty($signature)) {
            return false;
        }

        $secret = self::getWebhookSecret();
        if (empty($secret)) {
            return false;
        }

        $expected = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $signature);
    }

    /**
     * Fetch payment details from Razorpay
     */
    public function fetchPayment(string $paymentId): array
    {
        if (self::isMockMode()) {
            return [
                'id' => $paymentId,
                'entity' => 'payment',
                'status' => 'captured',
                'method' => 'upi',
                'vpa' => 'customer@okhdfcbank',
                'currency' => 'INR',
                'created_at' => time(),
                '_is_simulated' => true,
            ];
        }

        $response = Http::withBasicAuth(self::getKeyId(), self::getKeySecret())
            ->get("https://api.razorpay.com/v1/payments/{$paymentId}");

        if (! $response->successful()) {
            $err = $response->json('error.description') ?: 'Failed to fetch Razorpay payment';
            throw new \RuntimeException($err);
        }

        return $response->json();
    }
}
