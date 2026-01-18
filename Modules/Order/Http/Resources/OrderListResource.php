<?php

namespace Modules\Order\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderListResource extends JsonResource
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
        ];
    }
}
