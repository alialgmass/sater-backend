<?php

namespace Modules\Auth\Tests\Unit;

use Tests\TestCase;
use Modules\Auth\Services\AuthService;
use Modules\Auth\Services\TokenService;
use Modules\Auth\Repositories\CustomerRepositoryInterface;
use Modules\Auth\DTOs\RegisterCustomerData;
use Modules\Auth\DTOs\LoginData;
use Modules\Auth\Models\Customer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Mockery;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthServiceTest extends TestCase
{
    // use RefreshDatabase;

    protected $authService;
    protected $customerRepository;
    protected $tokenService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerRepository = Mockery::mock(CustomerRepositoryInterface::class);
        $this->tokenService = Mockery::mock(TokenService::class);

        $this->authService = new AuthService(
            $this->customerRepository,
            $this->tokenService
        );
    }

    public function test_register_creates_customer_and_returns_token()
    {
        $data = new RegisterCustomerData(
            name: 'John Doe',
            email: 'john@example.com',
            password: 'password'
        );

        $customer = new Customer();
        $customer->id = 1;
        $customer->email = 'john@example.com';

        $this->customerRepository->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($customer);

        $this->tokenService->shouldReceive('createToken')
            ->once()
            ->with($customer)
            ->andReturn('test-token');

        $result = $this->authService->register($data);

        $this->assertEquals($customer, $result['customer']);
        $this->assertEquals('test-token', $result['token']);
    }

    public function test_login_returns_token_with_valid_credentials()
    {
        $data = new LoginData(
            email: 'john@example.com',
            password: 'password'
        );

        $customer = new Customer();
        $customer->password = Hash::make('password');

        $this->customerRepository->shouldReceive('findByEmail')
            ->once()
            ->with('john@example.com')
            ->andReturn($customer);

        $this->tokenService->shouldReceive('createToken')
            ->once()
            ->with($customer, 'auth_token')
            ->andReturn('test-token');

        $result = $this->authService->login($data);

        $this->assertEquals($customer, $result['customer']);
        $this->assertEquals('test-token', $result['token']);
    }

    public function test_login_throws_exception_with_invalid_credentials()
    {
        $data = new LoginData(
            email: 'john@example.com',
            password: 'wrong-password'
        );

        $customer = new Customer();
        $customer->password = Hash::make('password');

        $this->customerRepository->shouldReceive('findByEmail')
            ->once()
            ->with('john@example.com')
            ->andReturn($customer);

        $this->expectException(ValidationException::class);

        $this->authService->login($data);
    }
}
