<?php

namespace App\Services\Payment;

use App\Models\Payment\Payment;
use App\DTOs\Payment\PaymentVerificationDTO;
use App\Interfaces\Payment\PaymentGatewayInterface;
use Illuminate\Support\Facades\Log;

class PaymentVerificationService
{
    public function __construct(
        protected PaymentGatewayFactory $gatewayFactory
    ) {}

    /**
     * Verify payment status with the gateway
     */
    public function verify(PaymentVerificationDTO $dto): array
    {
        try {
            // Get the payment record
            $payment = Payment::where('transaction_id', $dto->transactionId)
                ->where('reference_id', $dto->referenceId)
                ->first();

            if (!$payment) {
                return [
                    'success' => false,
                    'message' => 'Payment not found',
                ];
            }

            // Get the appropriate gateway
            $gateway = $this->gatewayFactory->getGateway($dto->gateway);

            // Verify the payment with the gateway
            $result = $gateway->verifyPayment($dto);

            // Update payment with verification result
            $payment->update([
                'gateway_response' => array_merge($payment->gateway_response ?? [], $result),
            ]);

            return [
                'success' => true,
                'payment_id' => $payment->id,
                'status' => $result['status'] ?? $payment->status->value,
                'transaction_id' => $payment->transaction_id,
                'reference_id' => $payment->reference_id,
                'verified_data' => $result,
            ];
        } catch (\Exception $e) {
            Log::error('Payment verification failed', [
                'error' => $e->getMessage(),
                'transaction_id' => $dto->transactionId,
                'reference_id' => $dto->referenceId,
            ]);

            return [
                'success' => false,
                'message' => 'Payment verification failed: ' . $e->getMessage(),
            ];
        }
    }
}