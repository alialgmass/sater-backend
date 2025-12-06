<?php

namespace App\Support\Traits;

trait MakeAble
{
    public static function make(...$arguments): static
    {
        return new static(...$arguments);
    }
}
