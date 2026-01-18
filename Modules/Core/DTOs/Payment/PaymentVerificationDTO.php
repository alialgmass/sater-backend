<?php

namespace App\DTOs\Payment;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use App\Enums\Payment\GatewayEnum;

class PaymentVerificationDTO extends Data
{
    public function __construct(
        #[Required]
        public readonly string $transactionId,
        
        #[Required]
        public readonly string $referenceId,
        
        #[Required]
        public readonly GatewayEnum $gateway,
        
        public readonly ?string $paymentId = null,
        public readonly ?array $additionalData = [],
    ) {}
    
    public static function fromArray(array $data): self
    {
        return new self(
            transactionId: $data['transaction_id'],
            referenceId: $data['reference_id'],
            gateway: GatewayEnum::from($data['gateway']),
            paymentId: $data['payment_id'] ?? null,
            additionalData: $data['additional_data'] ?? [],
        );
    }
}