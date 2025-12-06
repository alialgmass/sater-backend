<?php

namespace App\Http\Resources;

use App\Enums\CurrencyEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MoneyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'amount' => $this->resource->getAmount()->toFloat(),
            'currency' => CurrencyEnum::fromMoney($this->resource)->toResponse(),
            'formatted' => CurrencyEnum::format($this->resource),
        ];
    }
}
