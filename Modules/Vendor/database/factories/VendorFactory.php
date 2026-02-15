<?php

namespace Modules\Vendor\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Modules\Vendor\Models\Vendor;
use Modules\Vendor\Enums\VendorStatus;

class VendorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Vendor::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'password' => 'password', // Will be hashed by mutator
            'shop_name' => $this->faker->company(),
            'shop_slug' => $this->faker->unique()->slug(),
            'whatsapp' => $this->faker->phoneNumber(),
            'description' => $this->faker->paragraph(),
            'status' => VendorStatus::ACTIVE,
        ];
    }
}
