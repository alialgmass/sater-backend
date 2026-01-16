<?php

namespace Modules\Customer\DTOs;

use Modules\Customer\Enums\GenderEnum;

readonly class UpdateProfileData
{
    public function __construct(
        public ?string $first_name,
        public ?string $last_name,
        public ?string $date_of_birth,
        public ?GenderEnum $gender,
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            first_name: $request->validated('first_name'),
            last_name: $request->validated('last_name'),
            date_of_birth: $request->validated('date_of_birth'),
            gender: $request->filled('gender') ? GenderEnum::tryFrom($request->validated('gender')) : null,
        );
    }
}
