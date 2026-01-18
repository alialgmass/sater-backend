<?php

namespace Modules\Payment\Services;

use Modules\Payment\Models\Payment;
use Modules\Payment\Models\VendorPayment;
use Modules\Payment\Models\PaymentAttempt;
use Modules\Payment\Enums\PaymentStatusEnum;
use Modules\Payment\Enums\PaymentMethodEnum;
use Modules\Payment\DTOs\PaymentInitiationDTO;
use Modules\Payment\DTOs\PaymentVerificationDTO;
use Modules\Payment\Interfaces\PaymentGatewayInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct(
        protected PaymentInitiationService $initiationService,
        protected PaymentVerificationService $verificationService,
        protected PaymentWebhookService $webhookService,
        protected ReceiptService $receiptService
    ) {}

    /**
     * Process a payment initiation request
     */
    public function initiatePayment(PaymentInitiationDTO $dto): array
    {
        return DB::transaction(function () use ($dto) {
            // Create the payment record
            $payment = Payment::create([
                'customer_id' => $dto->customerId,
                'vendor_order_id' => $dto->vendorOrderId,
                'amount' => $dto->amount,
                'currency' => $dto->currency,
                'method' => $dto->method,
                'gateway' => $dto->gateway,
                'status' => PaymentStatusEnum::PENDING,
                'metadata' => $dto->metadata,
            ]);

            // Create payment attempt record
            $attempt = PaymentAttempt::create([
                'payment_id' => $payment->id,
                'attempt_number' => 1,
                'gateway' => $dto->gateway,
                'status' => PaymentStatusEnum::PENDING,
                'request_data' => $dto->toArray(),
            ]);

            // Update the payment with the attempt ID
            $payment->update(['last_payment_attempt_id' => $attempt->id]);

            // Handle COD differently from online payments
            if ($dto->method->isCashOnDelivery()) {
                return $this->handleCashOnDelivery($payment, $dto);
            } else {
                return $this->initiationService->processOnlinePayment($payment, $dto);
            }
        });
    }

    /**
     * Handle cash on delivery payment
     */
    protected function handleCashOnDelivery(Payment $payment, PaymentInitiationDTO $dto): array
    {
        // For COD, we just mark as pending and update vendor payment
        $payment->update([
            'status' => PaymentStatusEnum::PENDING,
            'transaction_id' => 'COD_' . uniqid(),
            'reference_id' => 'COD_REF_' . uniqid(),
        ]);

        // Update vendor payment status
        $vendorPayment = $this->getOrCreateVendorPayment($payment->vendor_order_id);
        $vendorPayment->update([
            'payment_status' => PaymentStatusEnum::PENDING,
        ]);

        return [
            'success' => true,
            'payment_id' => $payment->id,
            'status' => $payment->status->value,
            'transaction_id' => $payment->transaction_id,
            'reference_id' => $payment->reference_id,
            'redirect_url' => null,
            'message' => 'Cash on delivery payment initiated successfully',
        ];
    }

    /**
     * Verify a payment status
     */
    public function verifyPayment(PaymentVerificationDTO $dto): array
    {
        return $this->verificationService->verify($dto);
    }

    /**
     * Process a webhook from a payment gateway
     */
    public function handleWebhook(string $gateway, array $payload): array
    {
        return $this->webhookService->process($gateway, $payload);
    }

    /**
     * Get or create vendor payment record
     */
    protected function getOrCreateVendorPayment(int $vendorOrderId): VendorPayment
    {
        return VendorPayment::firstOrCreate(
            ['vendor_order_id' => $vendorOrderId],
            [
                'total_amount' => 0, // Will be updated later
                'payment_status' => PaymentStatusEnum::PENDING,
            ]
        );
    }

    /**
     * Process payment success
     */
    public function processPaymentSuccess(Payment $payment, array $gatewayResponse): void
    {
        DB::transaction(function () use ($payment, $gatewayResponse) {
            // Update payment status
            $payment->update([
                'status' => PaymentStatusEnum::COMPLETED,
                'gateway_response' => $gatewayResponse,
                'paid_at' => now(),
            ]);

            // Update the payment attempt
            $payment->attempts()->latest()->first()->update([
                'status' => PaymentStatusEnum::COMPLETED,
                'response_data' => $gatewayResponse,
                'processed_at' => now(),
            ]);

            // Update vendor payment
            $vendorPayment = $this->getOrCreateVendorPayment($payment->vendor_order_id);
            $vendorPayment->update([
                'payment_status' => PaymentStatusEnum::COMPLETED,
                'paid_at' => now(),
            ]);

            // Generate receipt
            dispatch(function () use ($payment) {
                $this->receiptService->generateAndSend($payment);
            });
        });
    }

    /**
     * Process payment failure
     */
    public function processPaymentFailure(Payment $payment, string $errorMessage, array $gatewayResponse = []): void
    {
        DB::transaction(function () use ($payment, $errorMessage, $gatewayResponse) {
            // Update payment status
            $payment->update([
                'status' => PaymentStatusEnum::FAILED,
                'failure_reason' => $errorMessage,
                'gateway_response' => $gatewayResponse,
            ]);

            // Update the payment attempt
            $payment->attempts()->latest()->first()->update([
                'status' => PaymentStatusEnum::FAILED,
                'response_data' => $gatewayResponse,
                'error_message' => $errorMessage,
                'processed_at' => now(),
            ]);

            // Update vendor payment
            $vendorPayment = $this->getOrCreateVendorPayment($payment->vendor_order_id);
            $vendorPayment->update([
                'payment_status' => PaymentStatusEnum::FAILED,
            ]);
        });
    }

    /**
     * Get payment status by order number
     */
    public function getPaymentStatusByOrderNumber(string $orderNumber): array
    {
        $vendorOrder = \Modules\Order\Models\VendorOrder::where('order_number', $orderNumber)->first();

        if (!$vendorOrder) {
            return [
                'success' => false,
                'message' => 'Order not found',
            ];
        }

        $payment = Payment::where('vendor_order_id', $vendorOrder->id)->latest()->first();

        if (!$payment) {
            return [
                'success' => false,
                'message' => 'No payment found for this order',
            ];
        }

        return [
            'success' => true,
            'payment_status' => $payment->status->value,
            'payment_method' => $payment->method->value,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'transaction_id' => $payment->transaction_id,
            'created_at' => $payment->created_at->toISOString(),
        ];
    }
}