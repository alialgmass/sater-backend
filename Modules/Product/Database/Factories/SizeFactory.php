<?php

namespace Modules\Product\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Product\Models\Size;

class SizeFactory extends Factory
{
    protected $model = Size::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'abbreviation' => strtoupper($this->faker->lexify('??')),
        ];
    }
}
