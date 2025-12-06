<?php

namespace App\Support\ModelServices;

use Illuminate\Database\Eloquent\Model;

/**
 * @property Model $model
 */
abstract class ModelService
{
    public function __construct(protected readonly Model $model) {}
}
