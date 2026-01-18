<?php

namespace Modules\Payment\DTOs;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Decimal;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\Required;
use Modules\Payment\Enums\PaymentMethodEnum;
use Modules\Payment\Enums\GatewayEnum;

class PaymentInitiationDTO extends Data
{
    public function __construct(
        #[Required]
        public readonly int $customerId,
        
        #[Required]
        public readonly int $vendorOrderId,
        
        #[Required]
        #[Decimal(2)]
        public readonly float $amount,
        
        #[Required]
        public readonly string $currency,
        
        #[Required]
        #[Enum(PaymentMethodEnum::class)]
        public readonly PaymentMethodEnum $method,
        
        #[Enum(GatewayEnum::class)]
        public readonly ?GatewayEnum $gateway = null,
        
        public readonly ?string $customerEmail = null,
        public readonly ?string $customerPhone = null,
        public readonly ?string $customerName = null,
        public readonly ?string $description = null,
        public readonly ?array $items = [],
        public readonly ?array $metadata = [],
        public readonly ?string $returnUrl = null,
        public readonly ?string $cancelUrl = null,
        public readonly ?string $callbackUrl = null,
    ) {}
    
    public static function fromArray(array $data): self
    {
        return new self(
            customerId: $data['customer_id'],
            vendorOrderId: $data['vendor_order_id'],
            amount: (float) $data['amount'],
            currency: $data['currency'],
            method: PaymentMethodEnum::from($data['method']),
            gateway: isset($data['gateway']) ? GatewayEnum::from($data['gateway']) : null,
            customerEmail: $data['customer_email'] ?? null,
            customerPhone: $data['customer_phone'] ?? null,
            customerName: $data['customer_name'] ?? null,
            description: $data['description'] ?? null,
            items: $data['items'] ?? [],
            metadata: $data['metadata'] ?? [],
            returnUrl: $data['return_url'] ?? null,
            cancelUrl: $data['cancel_url'] ?? null,
            callbackUrl: $data['callback_url'] ?? null,
        );
    }
}