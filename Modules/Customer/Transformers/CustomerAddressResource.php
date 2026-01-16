<?php

namespace Modules\Customer\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerAddressResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'country' => $this->country,
            'city' => $this->city,
            'area' => $this->area,
            'street' => $this->street,
            'building' => $this->building,
            'floor' => $this->floor,
            'apartment' => $this->apartment,
            'postal_code' => $this->postal_code,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_default' => $this->is_default,
        ];
    }
}
