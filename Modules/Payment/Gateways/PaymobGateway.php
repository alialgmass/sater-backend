<?php

namespace Modules\Payment\Gateways;

use Modules\Payment\Interfaces\PaymentGatewayInterface;
use Modules\Payment\DTOs\PaymentInitiationDTO;
use Modules\Payment\DTOs\PaymentVerificationDTO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PaymobGateway implements PaymentGatewayInterface
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $integrationId;

    public function __construct()
    {
        $this->baseUrl = config('services.paymob.base_url', 'https://accept.paymobsolutions.com/api');
        $this->apiKey = config('services.paymob.api_key');
        $this->integrationId = config('services.paymob.integration_id');
    }

    public function initiatePayment(PaymentInitiationDTO $dto): array
    {
        try {
            // Step 1: Get auth token
            $authResponse = Http::post("{$this->baseUrl}/auth/tokens", [
                'api_key' => $this->apiKey,
            ]);

            if (!$authResponse->successful()) {
                return [
                    'success' => false,
                    'error' => 'Authentication failed',
                    'message' => 'Unable to authenticate with Paymob',
                ];
            }

            $token = $authResponse->json()['token'];

            // Step 2: Register order
            $orderResponse = Http::post("{$this->baseUrl}/ecommerce/orders", [
                'auth_token' => $token,
                'delivery_needed' => 'false',
                'amount_cents' => (int) round($dto->amount * 100), // Convert to cents
                'currency' => $dto->currency,
                'items' => [],
            ]);

            if (!$orderResponse->successful()) {
                return [
                    'success' => false,
                    'error' => 'Order registration failed',
                    'message' => 'Unable to register order with Paymob',
                ];
            }

            $orderId = $orderResponse->json()['id'];

            // Step 3: Generate payment key
            $paymentKeyResponse = Http::post("{$this->baseUrl}/acceptance/payment_keys", [
                'auth_token' => $token,
                'amount_cents' => (int) round($dto->amount * 100), // Convert to cents
                'expiration' => 36000, // 10 hours
                'order_id' => $orderId,
                'billing_data' => [
                    'apartment' => 'NA',
                    'email' => $dto->customerEmail ?? 'unknown@example.com',
                    'floor' => 'NA',
                    'first_name' => $dto->customerName ?? 'Unknown',
                    'street' => 'NA',
                    'building' => 'NA',
                    'phone_number' => $dto->customerPhone ?? '+201000000000',
                    'shipping_method' => 'PKG',
                    'postal_code' => 'NA',
                    'city' => 'NA',
                    'country' => 'NA',
                    'last_name' => $dto->customerName ?? 'Unknown',
                    'state' => 'NA',
                ],
                'currency' => $dto->currency,
                'integration_id' => $this->integrationId,
                'lock_order_when_paid' => 'true',
            ]);

            if (!$paymentKeyResponse->successful()) {
                return [
                    'success' => false,
                    'error' => 'Payment key generation failed',
                    'message' => 'Unable to generate payment key with Paymob',
                ];
            }

            $paymentToken = $paymentKeyResponse->json()['token'];

            // Return payment URL for redirect
            $paymentUrl = "https://accept.paymobsolutions.com/api/acceptance/iframes/{$this->integrationId}?payment_token={$paymentToken}";

            return [
                'success' => true,
                'transaction_id' => $orderId,
                'reference_id' => $paymentToken,
                'payment_url' => $paymentUrl,
                'redirect_url' => $paymentUrl,
                'status' => 'pending',
                'message' => 'Payment initiated successfully',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Payment initiation failed',
            ];
        }
    }

    public function verifyPayment(PaymentVerificationDTO $dto): array
    {
        try {
            // Get auth token
            $authResponse = Http::post("{$this->baseUrl}/auth/tokens", [
                'api_key' => $this->apiKey,
            ]);

            if (!$authResponse->successful()) {
                return [
                    'success' => false,
                    'error' => 'Authentication failed',
                ];
            }

            $token = $authResponse->json()['token'];

            // Get transaction details
            $transactionResponse = Http::get("{$this->baseUrl}/acceptance/transactions/{$dto->transactionId}", [
                'auth_token' => $token,
            ]);

            if (!$transactionResponse->successful()) {
                return [
                    'success' => false,
                    'error' => 'Transaction not found',
                ];
            }

            $transaction = $transactionResponse->json();

            return [
                'success' => true,
                'status' => $transaction['success'] ? 'success' : 'failed',
                'transaction_id' => $transaction['id'],
                'reference_id' => $transaction['pending']['id'] ?? null,
                'amount' => $transaction['amount_cents'] / 100, // Convert from cents
                'currency' => $transaction['currency'],
                'source_data' => $transaction['source_data'],
                'is_void' => $transaction['is_void'],
                'is_refunded' => $transaction['is_refunded'],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Payment verification failed',
            ];
        }
    }

    public function handleWebhook(Request $request): array
    {
        $data = $request->all();

        // Verify the webhook signature if available
        if (!$this->validateSignature($request)) {
            return [
                'status' => 'error',
                'error_message' => 'Invalid signature',
            ];
        }

        // Process the webhook data
        $objType = $data['obj']['type'] ?? null;
        $objId = $data['obj']['id'] ?? null;
        $objState = $data['obj']['state'] ?? null;

        if ($objType === 'transaction') {
            if ($objState === 'captured') {
                return [
                    'status' => 'success',
                    'transaction_id' => $objId,
                    'reference_id' => $data['obj']['order']['id'] ?? null,
                    'amount' => ($data['obj']['amount_cents'] ?? 0) / 100, // Convert from cents
                    'currency' => $data['obj']['currency'],
                    'customer_email' => $data['obj']['billing_data']['email'] ?? null,
                ];
            } elseif ($objState === 'rejected') {
                return [
                    'status' => 'failed',
                    'transaction_id' => $objId,
                    'reference_id' => $data['obj']['order']['id'] ?? null,
                    'error_message' => $data['obj']['data']['message'] ?? 'Transaction rejected',
                ];
            }
        }

        return [
            'status' => 'ignored',
            'message' => 'Unhandled webhook event',
        ];
    }

    public function refund(string $transactionId, float $amount, ?string $reason = null): array
    {
        try {
            // Get auth token
            $authResponse = Http::post("{$this->baseUrl}/auth/tokens", [
                'api_key' => $this->apiKey,
            ]);

            if (!$authResponse->successful()) {
                return [
                    'success' => false,
                    'error' => 'Authentication failed',
                ];
            }

            $token = $authResponse->json()['token'];

            // Create refund
            $refundResponse = Http::post("{$this->baseUrl}/acceptance/refunds", [
                'auth_token' => $token,
                'transaction_id' => $transactionId,
                'amount_cents' => (int) round($amount * 100), // Convert to cents
                'reason' => $reason ?? 'Requested by customer',
            ]);

            if (!$refundResponse->successful()) {
                return [
                    'success' => false,
                    'error' => 'Refund creation failed',
                    'message' => 'Unable to create refund with Paymob',
                ];
            }

            $refund = $refundResponse->json();

            return [
                'success' => true,
                'refund_id' => $refund['id'],
                'status' => $refund['success'] ? 'completed' : 'failed',
                'amount' => $refund['amount_cents'] / 100, // Convert from cents
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Refund failed',
            ];
        }
    }

    public function supportsMethod(string $method): bool
    {
        return in_array($method, [
            'credit_card',
            'debit_card',
            'card',
            'wallet',
            'valu',
            'fawry',
            'kiosk',
            'sadad',
            'meezaqr',
            'meezacard',
        ]);
    }

    public function getConfig(): array
    {
        return [
            'name' => 'Paymob',
            'supports_cards' => true,
            'supports_wallets' => true,
            'supports_cod' => false,
            'currencies' => ['EGP', 'USD', 'EUR', 'GBP', 'SAR'],
        ];
    }

    public function validateSignature(Request $request): bool
    {
        // Paymob doesn't provide a standard signature header
        // We'll rely on the webhook secret verification
        $hmac = $request->header('X-Access-Token');
        $webhookSecret = config('services.paymob.hmac_secret');
        
        if (!$hmac || !$webhookSecret) {
            return false;
        }

        // For Paymob, we typically verify using the HMAC signature
        // This is a simplified version - in production, implement proper HMAC verification
        return true; // Placeholder - implement proper verification
    }
}