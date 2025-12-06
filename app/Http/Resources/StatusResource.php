<?php

namespace App\Http\Resources;

use App\Support\ModelState\BaseState;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use Modules\StateLog\Enums\StateLogCommentTranslationEnum;

/**
 * @mixin BaseState
 */
class StatusResource extends JsonResource
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
            'value' => $this->getValue(),
            'label' => $this->label(),
            'transitionable_states' => $this->transitionableStates(),
            'color' => [
                'value' => $this->color()->value,
                'class' => $this->color()->styleClass(),
                'hexadecimal' => $this->color()->hexadecimal(),
            ],
            'comment' => $this->translate($this->getModel()->lastLog?->comment),
        ];
    }

    private function translate($string): string
    {
        $tryEnum = StateLogCommentTranslationEnum::tryFrom(
            Str::of($string)
                ->lower()
                ->replace('-', '_')
                ->snake()->toString()
        );

        return match (true) {
            $tryEnum instanceof StateLogCommentTranslationEnum => $tryEnum->label(),
            default => (string) $string
        };
    }
}
