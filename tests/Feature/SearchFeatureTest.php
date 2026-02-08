<?php

namespace Tests\Feature\Search;

use App\DTOs\ProductSearchDTO;
use Modules\Category\Models\Category;
use Modules\Product\Models\Product;
use Modules\Product\Services\Search\SearchService;
use Modules\Vendor\Models\Vendor;
use Tests\TestCase;

class SearchFeatureTest extends TestCase
{
    protected SearchService $searchService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->searchService = app(SearchService::class);
    }

    /** @test */
    public function can_search_products_by_name()
    {
        // Create test data
        $vendor = Vendor::factory()->create();
        $category = Category::factory()->create();

        Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Cotton Hijab',
            'keywords' => 'hijab, cotton, modest',
        ]);

        $dto = ProductSearchDTO::from(['query' => 'cotton']);

        $results = $this->searchService->search($dto);

        $this->assertGreaterThan(0, $results->total());
    }

    /** @test */
    public function can_filter_by_price_range()
    {
        $vendor = Vendor::factory()->create();
        $category = Category::factory()->create();

        Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'price' => 50,
            'name' => 'Expensive Abaya',
        ]);

        Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'price' => 15,
            'name' => 'Cheap Scarf',
        ]);

        $dto = ProductSearchDTO::from([
            'price_min' => 10,
            'price_max' => 30,
        ]);

        $results = $this->searchService->search($dto);

        $this->assertEquals(1, $results->total());
    }

    /** @test */
    public function can_filter_in_stock_only()
    {
        $vendor = Vendor::factory()->create();
        $category = Category::factory()->create();

        Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'stock' => 0,
            'name' => 'Out of Stock Item',
        ]);

        Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'stock' => 10,
            'name' => 'In Stock Item',
        ]);

        $dto = ProductSearchDTO::from(['in_stock_only' => true]);

        $results = $this->searchService->search($dto);

        $this->assertEquals(1, $results->total());
    }

    /** @test */
    public function can_sort_by_price_ascending()
    {
        $vendor = Vendor::factory()->create();
        $category = Category::factory()->create();

        Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'price' => 100,
            'name' => 'Expensive',
        ]);

        Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'price' => 20,
            'name' => 'Cheap',
        ]);

        $dto = ProductSearchDTO::from(['sort' => 'price_asc']);

        $results = $this->searchService->search($dto);

        $products = $results->items();
        $this->assertLessThan($products[1]->price, $products[0]->price);
    }

    /** @test */
    public function can_sort_by_popularity()
    {
        $vendor = Vendor::factory()->create();
        $category = Category::factory()->create();

        Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'sales_count' => 10,
            'name' => 'Less Popular',
        ]);

        Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'sales_count' => 500,
            'name' => 'Very Popular',
        ]);

        $dto = ProductSearchDTO::from(['sort' => 'popularity']);

        $results = $this->searchService->search($dto);

        $products = $results->items();
        $this->assertGreaterThan($products[1]->sales_count, $products[0]->sales_count);
    }

    /** @test */
    public function can_get_autocomplete_suggestions()
    {
        $vendor = Vendor::factory()->create();
        $category = Category::factory()->create();

        Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Cotton Hijab',
            'keywords' => 'hijab, cotton',
        ]);

        $suggestions = $this->searchService->getAutocomplete('hijab', null, 10);

        $this->assertNotEmpty($suggestions);
    }

    /** @test */
    public function respects_pagination_limits()
    {
        $vendor = Vendor::factory()->create();
        $category = Category::factory()->create();

        Product::factory(15)->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'status' => 'active',
        ]);

        $dto = ProductSearchDTO::from(['per_page' => 10]);

        $results = $this->searchService->search($dto);

        $this->assertLessThanOrEqual(10, $results->count());
    }

    /** @test */
    public function limits_per_page_to_maximum()
    {
        $dto = ProductSearchDTO::from(['per_page' => 500]); // Try to exceed max

        $this->assertLessThanOrEqual(100, $dto->per_page); // Should be capped at 100
    }
}
