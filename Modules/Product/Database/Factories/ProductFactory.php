<?php

namespace Modules\Product\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Product\Models\Product;
use Modules\Category\Models\Category;
use Modules\Vendor\Models\Vendor;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $price = $this->faker->randomFloat(2, 10, 200);

        return [
            'vendor_id' => Vendor::factory(),
            'category_id' => Category::factory(),
            'name' => $this->faker->word() . ' ' . $this->faker->word(),
            'slug' => $this->faker->unique()->slug(),
            'description' => $this->faker->sentence(10),
            'sku' => $this->faker->unique()->bothify('SKU-????-####'),
            'price' => $price,
            'discounted_price' => $this->faker->boolean(30) ? $price * 0.8 : null,
            'stock' => $this->faker->numberBetween(0, 100),
            'keywords' => implode(',', $this->faker->words(5)),
            'sales_count' => $this->faker->numberBetween(0, 1000),
            'avg_rating' => $this->faker->randomFloat(2, 0, 5),
            'rating_count' => $this->faker->numberBetween(0, 500),
            'attributes' => [
                'size' => $this->faker->randomElement(['S', 'M', 'L', 'XL']),
                'color' => $this->faker->colorName(),
            ],
            'clothing_attributes' => [
                'fabric_type' => $this->faker->randomElement(['cotton', 'silk', 'polyester']),
                'sleeve_length' => $this->faker->randomElement(['sleeveless', 'half_sleeve', 'full_sleeve']),
                'opacity_level' => $this->faker->randomElement(['transparent', 'semi_transparent', 'opaque']),
            ],
            'status' => 'active',
        ];
    }

    /**
     * Indicate the product should be in stock
     */
    public function inStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => $this->faker->numberBetween(10, 100),
        ]);
    }

    /**
     * Indicate the product should be out of stock
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
        ]);
    }

    /**
     * Indicate the product should be popular
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'sales_count' => $this->faker->numberBetween(500, 5000),
            'avg_rating' => $this->faker->randomFloat(2, 4, 5),
            'rating_count' => $this->faker->numberBetween(100, 1000),
        ]);
    }
}
