<?php

namespace App\Services\Payment;

use App\Models\Payment\Payment;
use App\DTOs\Payment\PaymentInitiationDTO;
use App\Interfaces\Payment\PaymentGatewayInterface;
use Illuminate\Support\Facades\Log;

class PaymentInitiationService
{
    public function __construct(
        protected PaymentGatewayFactory $gatewayFactory
    ) {}

    /**
     * Process online payment through a gateway
     */
    public function processOnlinePayment(Payment $payment, PaymentInitiationDTO $dto): array
    {
        try {
            // Get the appropriate gateway
            $gateway = $this->gatewayFactory->getGateway($dto->gateway);

            // Prepare the initiation data
            $initiationData = [
                'customer_id' => $dto->customerId,
                'vendor_order_id' => $dto->vendorOrderId,
                'amount' => $dto->amount,
                'currency' => $dto->currency,
                'method' => $dto->method->value,
                'customer_email' => $dto->customerEmail,
                'customer_phone' => $dto->customerPhone,
                'customer_name' => $dto->customerName,
                'description' => $dto->description,
                'items' => $dto->items,
                'metadata' => $dto->metadata,
                'return_url' => $dto->returnUrl,
                'cancel_url' => $dto->cancelUrl,
                'callback_url' => $dto->callbackUrl,
            ];

            // Call the gateway to initiate payment
            $result = $gateway->initiatePayment(PaymentInitiationDTO::fromArray($initiationData));

            // Update payment with gateway response
            $payment->update([
                'transaction_id' => $result['transaction_id'] ?? null,
                'reference_id' => $result['reference_id'] ?? null,
                'gateway_response' => $result,
            ]);

            return [
                'success' => true,
                'payment_id' => $payment->id,
                'status' => $payment->status->value,
                'transaction_id' => $payment->transaction_id,
                'reference_id' => $payment->reference_id,
                'redirect_url' => $result['redirect_url'] ?? null,
                'payment_url' => $result['payment_url'] ?? null,
                'message' => $result['message'] ?? 'Payment initiated successfully',
            ];
        } catch (\Exception $e) {
            Log::error('Payment initiation failed', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->id,
                'vendor_order_id' => $dto->vendorOrderId,
            ]);

            // Update payment attempt with error
            $payment->attempts()->latest()->first()->update([
                'status' => \App\Enums\Payment\PaymentStatusEnum::FAILED,
                'error_message' => $e->getMessage(),
                'response_data' => ['error' => $e->getMessage()],
                'processed_at' => now(),
            ]);

            return [
                'success' => false,
                'message' => 'Payment initiation failed: ' . $e->getMessage(),
            ];
        }
    }
}