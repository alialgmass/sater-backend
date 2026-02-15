<?php

namespace Modules\Product\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Product\Models\Product;
use Modules\Category\Models\Category;
use Modules\Vendor\Models\Vendor;
use Modules\Product\Models\Color;
use Modules\Product\Models\Size;
use Modules\Product\Models\Tag;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function configure()
    {
        return $this->afterCreating(function (Product $product) {
            // Attach random colors
            $colors = Color::inRandomOrder()->take(rand(1, 3))->pluck('id');
            if ($colors->isEmpty()) {
                $colors = Color::factory()->count(3)->create()->pluck('id');
            }
            $product->colors()->attach($colors);

            // Attach random sizes
            $sizes = Size::inRandomOrder()->take(rand(1, 3))->pluck('id');
            if ($sizes->isEmpty()) {
                $sizes = Size::factory()->count(3)->create()->pluck('id');
            }
            $product->sizes()->attach($sizes);

            // Attach random tags
            $tags = Tag::inRandomOrder()->take(rand(1, 3))->pluck('id');
            if ($tags->isEmpty()) {
                $tags = Tag::factory()->count(3)->create()->pluck('id');
            }
            $product->tags()->attach($tags);
        });
    }

    public function definition(): array
    {
        $name = $this->faker->words(3, true);
        
        $colors = ['Red', 'Blue', 'Green', 'Black', 'White', 'Yellow'];
        $sizes = ['S', 'M', 'L', 'XL', 'XXL'];
        
        return [
            'vendor_id' => \Modules\Vendor\Models\Vendor::factory(),
            'category_id' => \Modules\Category\Models\Category::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraph(),
            'sku' => strtoupper(Str::random(8)),
            'price' => $this->faker->randomFloat(2, 10, 500),
            'discounted_price' => $this->faker->optional(0.3)->randomFloat(2, 5, 400),
            'stock' => $this->faker->numberBetween(0, 100),
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
