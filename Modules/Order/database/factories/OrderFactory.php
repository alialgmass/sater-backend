<?php

namespace Modules\Order\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use Modules\Order\Enums\OrderStatusEnum;
use Modules\Order\Enums\PaymentStatusEnum;
use Modules\Order\Models\Order;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'order_number' => $this->faker->unique()->numerify('ORD-########'),
            'customer_id' => User::factory(),
            'total_amount' => $this->faker->randomFloat(2, 100, 1000),
            'shipping_fees' => $this->faker->randomFloat(2, 10, 50),
            'payment_method' => 'cod',
            'payment_status' => PaymentStatusEnum::PENDING,
            'status' => OrderStatusEnum::PENDING,
            'shipping_address' => [
                'address' => $this->faker->streetAddress,
                'city' => $this->faker->city,
                'state' => $this->faker->state,
                'zip' => $this->faker->postcode,
            ],
        ];
    }
}
