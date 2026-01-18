<?php

namespace Modules\Order\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
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
            'order_number' => $this->order_number,
            'created_at' => $this->created_at->toDateTimeString(),
            'status' => $this->status,
            'total_amount' => $this->total_amount,
            'shipping_fees' => $this->shipping_fees,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'shipping_address' => $this->shipping_address,
            'vendor_orders' => VendorOrderResource::collection($this->whenLoaded('vendorOrders')),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
