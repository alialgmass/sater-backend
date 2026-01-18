<?php

namespace Modules\Payment\Gateways;

use Modules\Payment\Interfaces\PaymentGatewayInterface;
use Modules\Payment\DTOs\PaymentInitiationDTO;
use Modules\Payment\DTOs\PaymentVerificationDTO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class STCPayGateway implements PaymentGatewayInterface
{
    protected string $baseUrl;
    protected string $merchantId;
    protected string $terminalId;
    protected string $password;
    protected bool $sandbox;

    public function __construct()
    {
        $this->baseUrl = config('services.stcpay.base_url', 'https://sbpaymentservices.payments.osoul.dev');
        $this->merchantId = config('services.stcpay.merchant_id');
        $this->terminalId = config('services.stcpay.terminal_id');
        $this->password = config('services.stcpay.password');
        $this->sandbox = config('services.stcpay.sandbox', true);
    }

    public function initiatePayment(PaymentInitiationDTO $dto): array
    {
        try {
            // Prepare the payment request
            $requestData = [
                'MerchantId' => $this->merchantId,
                'TerminalId' => $this->terminalId,
                'Password' => $this->password,
                'Action' => '1', // Sale action
                'CurrencyCode' => $dto->currency,
                'TranAmount' => $dto->amount,
                'Udf1' => $dto->vendorOrderId, // User Defined Field 1
                'Udf2' => $dto->customerId, // User Defined Field 2
                'Udf3' => $dto->description ?: 'Order Payment',
                'RedirectURL' => $dto->returnUrl,
                'TrackId' => 'TRK_' . time() . '_' . $dto->vendorOrderId, // Unique track ID
                'MpiExtraParam' => [
                    'BillNumber' => 'BILL_' . $dto->vendorOrderId,
                    'CustomerReference' => $dto->customerEmail,
                    'CustomerName' => $dto->customerName,
                    'PhoneNumber' => $dto->customerPhone,
                ],
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/api/requestpayment", $requestData);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'error' => $response->body(),
                    'message' => 'Payment initiation failed',
                ];
            }

            $responseData = $response->json();

            if ($responseData['ReturnCode'] !== '000') {
                return [
                    'success' => false,
                    'error' => $responseData['ReturnMessage'] ?? 'Unknown error',
                    'message' => 'Payment initiation failed: ' . ($responseData['ReturnMessage'] ?? 'Unknown error'),
                ];
            }

            return [
                'success' => true,
                'transaction_id' => $responseData['TransactionId'] ?? null,
                'reference_id' => $responseData['TrackId'] ?? null,
                'payment_url' => $responseData['PaymentID'] ? $this->buildPaymentUrl($responseData['PaymentID']) : null,
                'redirect_url' => $responseData['PaymentID'] ? $this->buildPaymentUrl($responseData['PaymentID']) : null,
                'status' => 'pending',
                'message' => 'Payment initiated successfully',
            ];
        } catch (\Exception $e) {
            Log::error('STC Pay payment initiation failed', [
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
                'MerchantId' => $this->merchantId,
                'TerminalId' => $this->terminalId,
                'Password' => $this->password,
                'TransactionId' => $dto->transactionId,
                'Action' => '3', // Inquiry action
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/api/inquiry", $requestData);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'error' => $response->body(),
                    'message' => 'Payment verification failed',
                ];
            }

            $responseData = $response->json();

            if ($responseData['ReturnCode'] !== '000') {
                return [
                    'success' => false,
                    'error' => $responseData['ReturnMessage'] ?? 'Unknown error',
                    'message' => 'Payment verification failed: ' . ($responseData['ReturnMessage'] ?? 'Unknown error'),
                ];
            }

            return [
                'success' => true,
                'status' => $responseData['TranStatus'] ?? 'unknown',
                'transaction_id' => $dto->transactionId,
                'reference_id' => $dto->referenceId,
                'amount' => $responseData['TranAmount'] ?? null,
                'currency' => $responseData['CurrencyCode'] ?? null,
                'customer_email' => $responseData['CustomerReference'] ?? null,
                'payment_result' => $responseData,
            ];
        } catch (\Exception $e) {
            Log::error('STC Pay payment verification failed', [
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

        // Verify the signature if available
        if (!$this->validateSignature($request)) {
            return [
                'status' => 'error',
                'error_message' => 'Invalid signature',
            ];
        }

        // Process the webhook data
        $tranStatus = $data['TranStatus'] ?? null;
        $transactionId = $data['TransactionId'] ?? null;
        $trackId = $data['TrackId'] ?? null;
        $amount = $data['TranAmount'] ?? null;

        if ($tranStatus === 'CAPTURED') {
            return [
                'status' => 'success',
                'transaction_id' => $transactionId,
                'reference_id' => $trackId,
                'amount' => $amount,
                'currency' => $data['CurrencyCode'] ?? null,
                'customer_email' => $data['CustomerReference'] ?? null,
                'customer_phone' => $data['PhoneNumber'] ?? null,
            ];
        } elseif (in_array($tranStatus, ['FAILED', 'DECLINED', 'REVERSED', 'VOIDED'])) {
            return [
                'status' => 'failed',
                'transaction_id' => $transactionId,
                'reference_id' => $trackId,
                'error_message' => $data['ReturnMessage'] ?? "Transaction {$tranStatus}",
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
                'MerchantId' => $this->merchantId,
                'TerminalId' => $this->terminalId,
                'Password' => $this->password,
                'OriginalTransactionId' => $transactionId,
                'Action' => '4', // Refund action
                'CurrencyCode' => 'SAR', // STC Pay uses SAR
                'TranAmount' => $amount,
                'Reason' => $reason ?? 'Refund requested',
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/api/refund", $requestData);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'error' => $response->body(),
                    'message' => 'Refund failed',
                ];
            }

            $responseData = $response->json();

            if ($responseData['ReturnCode'] !== '000') {
                return [
                    'success' => false,
                    'error' => $responseData['ReturnMessage'] ?? 'Unknown error',
                    'message' => 'Refund failed: ' . ($responseData['ReturnMessage'] ?? 'Unknown error'),
                ];
            }

            return [
                'success' => true,
                'refund_id' => $responseData['TransactionId'] ?? null,
                'status' => $responseData['TranStatus'] ?? 'unknown',
                'amount' => $amount,
            ];
        } catch (\Exception $e) {
            Log::error('STC Pay refund failed', [
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
            'stc_pay',
            'mobile_money',
        ]);
    }

    public function getConfig(): array
    {
        return [
            'name' => 'STC Pay',
            'supports_cards' => false,
            'supports_wallets' => true,
            'supports_cod' => false,
            'currencies' => ['SAR'],
        ];
    }

    public function validateSignature(Request $request): bool
    {
        // STC Pay doesn't provide a standard signature header
        // We'll rely on the merchant credentials verification
        // In production, implement proper signature verification
        return true; // Placeholder - implement proper verification
    }

    /**
     * Build payment URL for STC Pay
     */
    protected function buildPaymentUrl(string $paymentId): string
    {
        return "{$this->baseUrl}/api/pay?id={$paymentId}";
    }
}