<?php

namespace Modules\Order\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VendorOrderListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'vendor_order_number' => $this->vendor_order_number,
            'status' => $this->status->value,
            'status_label' => ucfirst(str_replace('_', ' ', $this->status->value)),
            'total_amount' => $this->total_amount,
            'currency' => $this->currency,
            'is_cod' => $this->is_cod,
            'cod_amount' => $this->is_cod ? $this->cod_amount : null,
            'item_count' => $this->items->count(),
            'customer_name' => $this->masterOrder->customer->name ?? null,
            'customer_phone' => $this->masterOrder->customer->phone ?? null,
            'shipping_address' => $this->shipping_address,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            'confirmed_at' => $this->confirmed_at?->toISOString(),
            'shipped_at' => $this->shipped_at?->toISOString(),
            'delivered_at' => $this->delivered_at?->toISOString(),
            'fulfillment_duration' => $this->fulfillment_duration,
        ];
    }
}