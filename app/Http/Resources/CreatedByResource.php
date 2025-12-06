<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\ActionEvent\Enums\ActionEventNameEnum;

/**
 * @mixin User
 */
class CreatedByResource extends JsonResource
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
            'name' => $this->name instanceof ActionEventNameEnum ? $this->name->label : $this->name,
            'email' => $this->email,
            'username' => $this->username,
            'phone' => $this->phone,
        ];
    }
}
