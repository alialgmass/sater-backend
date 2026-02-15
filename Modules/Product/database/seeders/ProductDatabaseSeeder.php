<?php

namespace Modules\Product\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Category\Models\Category;
use Modules\Product\Models\Product;
use Modules\Vendor\Models\Vendor;

class ProductDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            // 2. Create hierarchical categories
            $categories = Category::factory()->count(10)->create();
            
            foreach ($categories as $category) {
                Category::factory()->count(3)->create([
                    'parent_id' => $category->id
                ]);
            }

            // 3. Create products for each category
            $allCategories = Category::all();
            $vendors = \Modules\Vendor\Models\Vendor::all();

            if ($vendors->isEmpty()) {
                $vendors = \Modules\Vendor\Models\Vendor::factory()->count(5)->create();
            }

            $allCategories->each(function ($category) use ($vendors) {
                try {
                    Product::factory()->count(rand(3, 8))->create([
                        'category_id' => $category->id,
                        'vendor_id' => $vendors->random()->id,
                    ]);
                } catch (\Exception $e) {
                    echo "Failed to create product for category {$category->id}: " . $e->getMessage() . "\n";
                }
            });
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
            echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
            echo $e->getTraceAsString();
        }
    }
}
