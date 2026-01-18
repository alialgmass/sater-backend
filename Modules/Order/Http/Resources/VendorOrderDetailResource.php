<?php

namespace Modules\Order\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VendorOrderDetailResource extends JsonResource
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
            'cod_confirmed' => $this->cod_confirmed ?? false,
            'customer' => [
                'name' => $this->masterOrder->customer->name ?? null,
                'phone' => $this->masterOrder->customer->phone ?? null,
                // Note: Email is not exposed for privacy unless specifically allowed
            ],
            'delivery_address' => $this->shipping_address,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'payment_method' => $this->masterOrder->payment_method ?? null,
            'payment_status' => $this->masterOrder->payment_status?->value ?? null,
            'notes' => $this->notes,
            'shipment' => $this->whenLoaded('shipment', function () {
                return $this->shipment ? [
                    'courier_name' => $this->shipment->courier_name,
                    'tracking_number' => $this->shipment->tracking_number,
                    'tracking_url' => $this->shipment->tracking_url,
                ] : null;
            }),
            'timestamps' => [
                'created_at' => $this->created_at->toISOString(),
                'confirmed_at' => $this->confirmed_at?->toISOString(),
                'processing_started_at' => $this->processing_started_at?->toISOString(),
                'packed_at' => $this->packed_at?->toISOString(),
                'shipped_at' => $this->shipped_at?->toISOString(),
                'delivered_at' => $this->delivered_at?->toISOString(),
            ],
            'fulfillment_duration' => $this->fulfillment_duration,
        ];
    }
}