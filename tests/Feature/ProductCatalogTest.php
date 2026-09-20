<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    private function customer(): Customer
    {
        return Customer::create([
            'name' => 'Farooq', 'phone' => '9999999999',
            'password' => 'Passw0rd1!', 'status' => 'active',
        ]);
    }

    public function test_catalog_lists_only_active_sellables_with_price_fallback(): void
    {
        $customer = $this->customer();

        Product::create([
            'name' => 'Urea 45kg', 'unit' => 'bag', 'type' => 'sellable',
            'rate' => 300, 'mrp' => 350, 'selling_price' => 320, 'stock_qty' => 10,
        ]);
        Product::create([
            'name' => 'DAP 50kg', 'unit' => 'bag', 'type' => 'sellable',
            'rate' => 1200, 'stock_qty' => 0,
        ]);
        Product::create([
            'name' => 'M9 Rootstock', 'unit' => 'pcs', 'type' => 'material',
            'rate' => 150, 'stock_qty' => 500,
        ]);
        Product::create([
            'name' => 'Old Pesticide', 'unit' => 'ltr', 'type' => 'sellable',
            'rate' => 500, 'stock_qty' => 5, 'is_active' => false,
        ]);

        $response = $this->actingAs($customer, 'sanctum')->getJson('/api/products')->assertOk();

        $products = $response->json('products');
        $this->assertCount(2, $products);

        $urea = collect($products)->firstWhere('name', 'Urea 45kg');
        $this->assertEquals(350.0, $urea['mrp']);
        $this->assertEquals(320.0, $urea['price']);
        $this->assertTrue($urea['in_stock']);

        // No selling_price → falls back to rate.
        $dap = collect($products)->firstWhere('name', 'DAP 50kg');
        $this->assertNull($dap['mrp']);
        $this->assertEquals(1200.0, $dap['price']);
        $this->assertFalse($dap['in_stock']);
    }

    public function test_catalog_supports_search_and_requires_auth(): void
    {
        $customer = $this->customer();

        Product::create([
            'name' => 'Urea 45kg', 'unit' => 'bag', 'type' => 'sellable',
            'rate' => 300, 'stock_qty' => 10,
        ]);

        $this->getJson('/api/products')->assertUnauthorized();

        $this->actingAs($customer, 'sanctum')->getJson('/api/products?q=urea')
            ->assertOk()
            ->assertJsonCount(1, 'products');

        $this->actingAs($customer, 'sanctum')->getJson('/api/products?q=zzz-no-match')
            ->assertOk()
            ->assertJsonCount(0, 'products');
    }
}
