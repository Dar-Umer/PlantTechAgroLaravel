<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_via_api(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Bashir Ahmad Wani',
            'phone' => '9419011223',
            'password' => 'secret123',
            'email' => 'bashir@example.com',
            'address' => 'Dangerpora, Sopore',
            'area' => 'Baramulla',
            'orchard_name' => 'Wani Apple Estate',
            'kanals' => 12.5,
            'plants_count' => 850,
            'variety' => 'Gala & Fuji High Density',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('message', 'Registration successful! Welcome to Plant Tech Agro.');
        $response->assertJsonStructure([
            'token',
            'user' => [
                'id',
                'orchardist_id',
                'name',
                'phone',
                'email',
                'address',
                'area',
                'status',
                'orchards_count',
            ],
            'app_config',
        ]);

        $this->assertDatabaseHas('customers', [
            'name' => 'Bashir Ahmad Wani',
            'phone' => '9419011223',
            'area' => 'Baramulla',
        ]);

        $customer = Customer::where('phone', '9419011223')->first();
        $this->assertNotNull($customer);
        $this->assertNotEmpty($customer->orchardist_id);
        $this->assertStringStartsWith('OID-', $customer->orchardist_id);

        // Verify initial orchard was created
        $this->assertDatabaseHas('orchards', [
            'customer_id' => $customer->id,
            'name' => 'Wani Apple Estate',
            'area_kanals' => 12.5,
            'tree_count' => 850,
        ]);
    }

    public function test_customer_registration_rejects_duplicate_phone(): void
    {
        Customer::create([
            'name' => 'Existing Farmer',
            'phone' => '9419999888',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Another Farmer',
            'phone' => '9419999888',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['phone']);
    }

    public function test_customer_login_returns_orchardist_id(): void
    {
        $customer = Customer::create([
            'name' => 'Tariq Lone',
            'phone' => '9876543210',
            'password' => 'secret123',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'phone' => '9876543210',
            'password' => 'secret123',
        ]);

        $response->assertOk();
        $response->assertJsonPath('user.orchardist_id', $customer->orchardist_id);
    }
}
