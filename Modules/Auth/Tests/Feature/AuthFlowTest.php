<?php

namespace Modules\Auth\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Modules\Customer\Models\Customer;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        try {
            parent::setUp();
            $this->artisan('migrate', [
                '--path' => base_path('Modules/Customer/database/migrations'),
                '--realpath' => true,
            ]);
        } catch (\Throwable $e) {
            fwrite(STDERR, $e->getMessage() . "\n" . $e->getTraceAsString());
            exit(1);
        }
    }

    public function test_can_register_customer()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'customer',
                    'token',
                ],
            ]);

        $this->assertDatabaseHas('customers', [
            'email' => 'john@example.com',
        ]);
    }

    public function test_can_login_customer()
    {
        $customer = Customer::create([
            'name' => 'John Login',
            'email' => 'login@example.com',
            'password' => bcrypt('password'),
            'phone' => '0987654321',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $customer->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'customer',
                    'token',
                ],
            ]);
    }

    public function test_can_logout_customer()
    {
        $customer = Customer::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
            'phone' => '1234567890',
        ]);
        Sanctum::actingAs($customer, ['*']);

        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logged out successfully.',
            ]);
    }

    public function test_cannot_access_protected_route_without_token()
    {
        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(401);
    }
}
