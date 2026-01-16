<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Modules\Product\Models\Product;
use Modules\Category\Models\Category;
use Modules\Vendor\Models\Vendor;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class SearchApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /** @test */
    public function can_search_products_via_api()
    {
        $vendor = Vendor::factory()->create();
        $category = Category::factory()->create();

        Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Cotton Hijab Blue',
            'keywords' => 'hijab,cotton,blue',
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/v1/search/products?query=hijab');

        $response->assertOk();
        $response->assertJsonStructure([
            'products' => [
                '*' => [
                    'id',
                    'name',
                    'price',
                    'rating',
                    'vendor',
                    'category',
                ]
            ]
        ]);
    }

    /** @test */
    public function search_validates_input_parameters()
    {
        $response = $this->getJson('/api/v1/search/products?price_min=invalid');

        $response->assertUnprocessable();
    }

    /** @test */
    public function search_respects_pagination()
    {
        $vendor = Vendor::factory()->create();
        $category = Category::factory()->create();

        Product::factory(25)->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/v1/search/products?per_page=10');

        $response->assertOk();
        $response->assertJsonCount(10, 'products.data');
        $response->assertJsonPath('pagination.per_page', 10);
    }

    /** @test */
    public function autocomplete_returns_suggestions()
    {
        $vendor = Vendor::factory()->create();
        $category = Category::factory()->create();

        Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Cotton Hijab',
            'keywords' => 'hijab,cotton',
            'status' => 'active',
            'stock' => 10,
        ]);

        $response = $this->getJson('/api/v1/search/autocomplete?query=hij');

        $response->assertOk();
        $response->assertJsonStructure([
            'suggestions' => [
                '*' => ['text', 'type']
            ]
        ]);
    }

    /** @test */
    public function autocomplete_requires_minimum_query_length()
    {
        $response = $this->getJson('/api/v1/search/autocomplete?query=h');

        $response->assertUnprocessable();
    }

    /** @test */
    public function authenticated_user_can_view_search_history()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/search/history');

        $response->assertOk();
        $response->assertJsonStructure([
            'history' => [
                '*' => [
                    'id',
                    'query',
                    'results_count',
                    'searched_at',
                ]
            ]
        ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_view_search_history()
    {
        $response = $this->getJson('/api/v1/search/history');

        $response->assertUnauthorized();
    }

    /** @test */
    public function can_clear_search_history()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // First record a search
        $this->postJson('/api/v1/search/products', ['query' => 'test']);

        // Then clear history
        $response = $this->deleteJson('/api/v1/search/history');

        $response->assertOk();

        // Verify history is cleared
        $historyResponse = $this->getJson('/api/v1/search/history');
        $historyResponse->assertJsonCount(0, 'history');
    }

    /** @test */
    public function can_search_vendor_store()
    {
        $vendor = Vendor::factory()->create();
        $category = Category::factory()->create();

        Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Cotton Abaya',
            'status' => 'active',
        ]);

        $response = $this->getJson("/api/v1/vendors/{$vendor->id}/search?query=abaya");

        $response->assertOk();
        $response->assertJsonPath('vendor.id', $vendor->id);
    }

    /** @test */
    public function vendor_search_returns_404_for_inactive_vendor()
    {
        $vendor = Vendor::factory()->create(['status' => 'suspended']);

        $response = $this->getJson("/api/v1/vendors/{$vendor->id}/search?query=test");

        $response->assertNotFound();
    }

    /** @test */
    public function search_with_filters()
    {
        $vendor = Vendor::factory()->create();
        $category = Category::factory()->create();

        Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'price' => 25,
            'name' => 'Cheap Hijab',
            'status' => 'active',
        ]);

        Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'price' => 100,
            'name' => 'Expensive Abaya',
            'status' => 'active',
        ]);

        $response = $this->getJson(
            '/api/v1/search/products?' .
            'price_min=10&price_max=50'
        );

        $response->assertOk();
        $this->assertEquals(1, count($response->json('products')));
    }

    /** @test */
    public function no_results_includes_suggestions()
    {
        $response = $this->getJson('/api/v1/search/products?query=nonexistent');

        $response->assertStatus(202); // Accepted but no content
        $response->assertJsonStructure([
            'products' => [],
            'suggestions' => []
        ]);
    }
}
