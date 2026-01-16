<?php

namespace Modules\Customer\Values; // Using Transformers or Resources usually

namespace Modules\Customer\Transformers; // Standard Laravel Modules structure usually uses Transformers or Http/Resources

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerProfileResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->customer->email,
            'phone' => $this->customer->phone,
            'date_of_birth' => $this->date_of_birth,
            'gender' => $this->gender,
            'share_phone' => $this->customer->privacySettings?->share_phone ?? false,
            // Add other privacy controlled fields if needed
        ];
    }
}
