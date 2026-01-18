<?php

namespace Modules\Order\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VendorOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'vendor' => $this->vendor->name, // Assuming vendor has a name attribute
            'status' => $this->status,
            'total_amount' => $this->total_amount,
            'currency' => $this->currency,
            'shipment' => new ShipmentResource($this->whenLoaded('shipment')),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
