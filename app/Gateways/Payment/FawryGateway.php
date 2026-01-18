<?php

namespace App\Gateways\Payment;

use App\Interfaces\Payment\PaymentGatewayInterface;
use App\DTOs\Payment\PaymentInitiationDTO;
use App\DTOs\Payment\PaymentVerificationDTO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FawryGateway implements PaymentGatewayInterface
{
    protected string $baseUrl;
    protected string $merchantCode;
    protected string $securityKey;

    public function __construct()
    {
        $this->baseUrl = config('services.fawry.base_url', 'https://atfawry.fawrystaging.com');
        $this->merchantCode = config('services.fawry.merchant_code');
        $this->securityKey = config('services.fawry.security_key');
    }

    public function initiatePayment(PaymentInitiationDTO $dto): array
    {
        try {
            // Prepare the request data
            $requestData = [
                'merchantCode' => $this->merchantCode,
                'customerProfileId' => $dto->customerId,
                'customerMobile' => $dto->customerPhone,
                'customerEmail' => $dto->customerEmail,
                'language' => 'en',
                'paymentMethod' => 'WALLET',
                'chargeItems' => [
                    [
                        'itemId' => $dto->vendorOrderId,
                        'description' => $dto->description ?: 'Order Payment',
                        'price' => $dto->amount,
                        'quantity' => 1,
                    ]
                ],
                'merchantRefNumber' => 'ORDER_' . $dto->vendorOrderId . '_' . time(),
                'customerName' => $dto->customerName,
            ];

            // Calculate signature
            $signature = $this->calculateSignature($requestData);
            $requestData['signature'] = $signature;

            // Make the API call
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/fawry/payments/pay", $requestData);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'error' => $response->body(),
                    'message' => 'Payment initiation failed',
                ];
            }

            $responseData = $response->json();

            return [
                'success' => true,
                'transaction_id' => $responseData['paymentId'] ?? null,
                'reference_id' => $responseData['merchantRefNumber'] ?? null,
                'payment_url' => $responseData['paymentLink'] ?? null,
                'redirect_url' => $responseData['paymentLink'] ?? null,
                'status' => $responseData['statusCode'] === '200' ? 'pending' : 'failed',
                'message' => $responseData['message'] ?? 'Payment initiated successfully',
            ];
        } catch (\Exception $e) {
            Log::error('Fawry payment initiation failed', [
                'error' => $e->getMessage(),
                'customer_id' => $dto->customerId,
                'vendor_order_id' => $dto->vendorOrderId,
            ]);

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
            $requestData = [
                'merchantCode' => $this->merchantCode,
                'merchantRefNumber' => $dto->referenceId,
            ];

            $signature = $this->calculateSignature($requestData);
            $requestData['signature'] = $signature;

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/fawry/payments/status", $requestData);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'error' => $response->body(),
                    'message' => 'Payment verification failed',
                ];
            }

            $responseData = $response->json();

            return [
                'success' => true,
                'status' => $responseData['paymentStatus'] ?? 'unknown',
                'transaction_id' => $dto->transactionId,
                'reference_id' => $dto->referenceId,
                'amount' => $responseData['amount'] ?? null,
                'currency' => 'EGP', // Fawry uses EGP
                'customer_email' => $responseData['customerEmail'] ?? null,
                'customer_mobile' => $responseData['customerMobile'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Fawry payment verification failed', [
                'error' => $e->getMessage(),
                'transaction_id' => $dto->transactionId,
                'reference_id' => $dto->referenceId,
            ]);

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

        // Verify the signature
        if (!$this->validateSignature($request)) {
            return [
                'status' => 'error',
                'error_message' => 'Invalid signature',
            ];
        }

        // Process the webhook data
        $paymentStatus = $data['paymentStatus'] ?? null;
        $merchantRefNumber = $data['merchantRefNumber'] ?? null;
        $fawryRefNumber = $data['fawryRefNumber'] ?? null;
        $amount = $data['amount'] ?? null;

        if ($paymentStatus === 'PAID') {
            return [
                'status' => 'success',
                'transaction_id' => $fawryRefNumber,
                'reference_id' => $merchantRefNumber,
                'amount' => $amount,
                'currency' => 'EGP',
                'customer_email' => $data['customerEmail'] ?? null,
                'customer_mobile' => $data['customerMobile'] ?? null,
            ];
        } elseif (in_array($paymentStatus, ['REJECTED', 'CANCELLED', 'EXPIRED'])) {
            return [
                'status' => 'failed',
                'transaction_id' => $fawryRefNumber,
                'reference_id' => $merchantRefNumber,
                'error_message' => $data['reason'] ?? "Payment {$paymentStatus}",
            ];
        }

        return [
            'status' => 'ignored',
            'message' => 'Unhandled webhook event',
        ];
    }

    public function refund(string $transactionId, float $amount, ?string $reason = null): array
    {
        try {
            $requestData = [
                'merchantCode' => $this->merchantCode,
                'fawryRefNumber' => $transactionId,
                'refundAmount' => $amount,
                'customerEmail' => $reason ?? 'Refund requested',
            ];

            $signature = $this->calculateSignature($requestData);
            $requestData['signature'] = $signature;

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/fawry/payments/refund", $requestData);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'error' => $response->body(),
                    'message' => 'Refund failed',
                ];
            }

            $responseData = $response->json();

            return [
                'success' => true,
                'refund_id' => $responseData['refundReferenceNumber'] ?? null,
                'status' => $responseData['statusCode'] === '200' ? 'completed' : 'failed',
                'amount' => $amount,
            ];
        } catch (\Exception $e) {
            Log::error('Fawry refund failed', [
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId,
                'amount' => $amount,
            ]);

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
            'wallet',
            'fawry',
        ]);
    }

    public function getConfig(): array
    {
        return [
            'name' => 'Fawry',
            'supports_cards' => false,
            'supports_wallets' => true,
            'supports_cod' => false,
            'currencies' => ['EGP'],
        ];
    }

    public function validateSignature(Request $request): bool
    {
        $signature = $request->header('Signature');
        $data = $request->all();

        if (!$signature) {
            return false;
        }

        // Calculate expected signature
        $expectedSignature = $this->calculateSignature($data);

        // Compare signatures
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Calculate signature for Fawry API calls
     */
    protected function calculateSignature(array $data): string
    {
        // Sort the data by key
        ksort($data);

        // Create a string by concatenating key=value pairs
        $signatureString = '';
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $signatureString .= $key . '=' . json_encode($value) . '&';
            } else {
                $signatureString .= $key . '=' . $value . '&';
            }
        }

        // Remove the trailing '&'
        $signatureString = rtrim($signatureString, '&');

        // Append the security key
        $signatureString .= $this->securityKey;

        // Hash the string using SHA256
        return hash('sha256', $signatureString);
    }
}