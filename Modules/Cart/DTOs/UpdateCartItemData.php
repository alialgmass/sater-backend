<?php

namespace Modules\Cart\DTOs;

readonly class UpdateCartItemData
{
    public function __construct(
        public int $quantity,
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            quantity: $request->validated('quantity'),
        );
    }
}
