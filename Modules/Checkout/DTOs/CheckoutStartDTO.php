<?php

namespace Modules\Checkout\DTOs;

readonly class CheckoutStartDTO
{
    public function __construct(
        public string $email,
        public string $phone,
        public ?int $customer_id = null,
        public ?string $cart_key = null,
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            email: $request->validated('email'),
            phone: $request->validated('phone'),
            customer_id: $request->user('api_customers')?->id,
            cart_key: $request->header('X-Cart-Key') ?? $request->input('cart_key'),
        );
    }
}
