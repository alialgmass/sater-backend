<?php

namespace Modules\Order\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentResource extends JsonResource
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
            'courier_name' => $this->courier_name,
            'tracking_number' => $this->tracking_number,
            'tracking_url' => $this->tracking_url,
        ];
    }
}
