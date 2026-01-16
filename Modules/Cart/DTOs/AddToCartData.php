<?php

namespace Modules\Cart\DTOs;

readonly class AddToCartData
{
    public function __construct(
        public int $product_id,
        public int $quantity = 1,
        public ?string $cart_key = null, // For guest users
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            product_id: $request->validated('product_id'),
            quantity: $request->validated('quantity', 1),
            cart_key: $request->header('X-Cart-Key') ?? $request->input('cart_key'),
        );
    }
}
