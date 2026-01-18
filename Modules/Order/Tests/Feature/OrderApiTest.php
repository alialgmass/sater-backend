<?php

namespace Modules\Order\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Modules\Order\Models\Order;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Additional setup can go here
    }

    public function test_guest_cannot_access_orders_endpoints()
    {
        $response = $this->getJson('/api/orders');
        $response->assertUnauthorized();

        $response = $this->getJson('/api/orders/some-order');
        $response->assertUnauthorized();

        $response = $this->postJson('/api/orders/some-order/cancel');
        $response->assertUnauthorized();

        $response = $this->postJson('/api/orders/some-order/reorder');
        $response->assertUnauthorized();

        $response = $this->getJson('/api/orders/some-order/invoice');
        $response->assertUnauthorized();
    }
    
    public function test_customer_can_view_their_own_orders()
    {
        $customer = User::factory()->create();
        $order = Order::factory()->for($customer, 'customer')->create();

        Sanctum::actingAs($customer);

        $response = $this->getJson('/api/orders');

        $response->assertOk();
        $response->assertJsonFragment(['order_number' => $order->order_number]);
    }

    public function test_customer_cannot_view_other_customers_orders()
    {
        $customer1 = User::factory()->create();
        $customer2 = User::factory()->create();
        $order = Order::factory()->for($customer2, 'customer')->create();

        Sanctum::actingAs($customer1);

        $response = $this->getJson("/api/orders/{$order->order_number}");

        $response->assertForbidden();
    }
}
