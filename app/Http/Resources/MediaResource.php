<?php

namespace App\Http\Resources;

use App\Support\Media\Media;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Media
 */
class MediaResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'name' => $this->file_name,
            'url' => $this->getUrl(),
            'size' => $this->size,
            'extension' => $this->extension,
        ];
    }
}
