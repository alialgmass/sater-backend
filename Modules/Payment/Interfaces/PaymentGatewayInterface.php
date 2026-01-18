<?php

namespace Modules\Payment\Interfaces;

use Modules\Payment\DTOs\PaymentInitiationDTO;
use Modules\Payment\DTOs\PaymentVerificationDTO;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    /**
     * Initialize a payment with the gateway
     */
    public function initiatePayment(PaymentInitiationDTO $dto): array;

    /**
     * Verify payment status with the gateway
     */
    public function verifyPayment(PaymentVerificationDTO $dto): array;

    /**
     * Handle webhook callback from the gateway
     */
    public function handleWebhook(Request $request): array;

    /**
     * Refund a payment
     */
    public function refund(string $transactionId, float $amount, ?string $reason = null): array;

    /**
     * Check if the gateway supports a specific payment method
     */
    public function supportsMethod(string $method): bool;

    /**
     * Get gateway configuration
     */
    public function getConfig(): array;

    /**
     * Validate webhook signature
     */
    public function validateSignature(Request $request): bool;
}