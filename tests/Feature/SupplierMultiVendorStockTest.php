<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\PurchaseBill;
use App\Models\Supplier;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierMultiVendorStockTest extends TestCase
{
    use RefreshDatabase;

    private function actingAdmin(): Admin
    {
        $this->seed(AdminSeeder::class);

        return Admin::where('email', 'admin@pta.com')->first();
    }

    public function test_same_product_supplied_by_multiple_suppliers_preserves_ownership_and_per_vendor_stock(): void
    {
        $admin = $this->actingAdmin();

        // 1. Create Supplier A and Supplier B
        $supplierA = Supplier::create([
            'name' => 'Supplier A',
            'phone' => '9876543210',
            'is_active' => true,
        ]);

        $supplierB = Supplier::create([
            'name' => 'Supplier B',
            'phone' => '9876543220',
            'is_active' => true,
        ]);

        // 2. Product 1 is created with Supplier A as primary supplier and 10 pcs initial stock
        $product = Product::create([
            'name' => 'Gala Apple Tree',
            'unit' => 'pcs',
            'rate' => 300,
            'stock_qty' => 10,
            'supplier_id' => $supplierA->id,
            'is_active' => true,
        ]);

        // Initial batch for Supplier A
        ProductBatch::create([
            'product_id' => $product->id,
            'batch_number' => 'LOT-SUP-A',
            'inward_date' => now()->subDays(5)->format('Y-m-d'),
            'initial_qty' => 10,
            'current_qty' => 10,
            'unit_cost' => 200,
            'supplier_id' => $supplierA->id,
            'status' => ProductBatch::STATUS_ACTIVE,
        ]);

        // Verify initial state
        $this->assertEquals(10, (float) $product->stock_qty);
        $this->assertEquals($supplierA->id, $product->supplier_id);

        $suppliedA = $supplierA->getSuppliedProducts();
        $this->assertCount(1, $suppliedA);
        $this->assertEquals(10, (float) $suppliedA->first()->supplier_stock);
        $this->assertEquals(10, (float) $suppliedA->first()->stock_qty);

        $suppliedB = $supplierB->getSuppliedProducts();
        $this->assertCount(0, $suppliedB);

        // 3. Supplier B supplies 5 pieces of the SAME product via Purchase Bill
        $billData = [
            'supplier_id' => $supplierB->id,
            'bill_date' => now()->format('Y-m-d'),
            'discount' => 0,
            'shipping_cost' => 0,
            'items' => [
                [
                    'product_id' => $product->id,
                    'batch_number' => 'LOT-SUP-B',
                    'quantity' => 5,
                    'unit_cost' => 210,
                    'tax_percent' => 0,
                ]
            ],
        ];

        $response = $this->actingAs($admin, 'admin')->post('/admin/purchase-bills', $billData);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // 4. Assertions on Product
        $product->refresh();
        // Product's primary supplier MUST NOT be overwritten by Supplier B!
        $this->assertEquals($supplierA->id, $product->supplier_id, 'Primary supplier was overwritten by second supplier bill!');
        // Total warehouse stock is now 10 + 5 = 15
        $this->assertEquals(15, (float) $product->stock_qty);

        // 5. Assertions on Supplier A
        $suppliedAAfter = $supplierA->getSuppliedProducts();
        $this->assertCount(1, $suppliedAAfter, 'Supplier A lost the product after Supplier B purchased it!');
        $prodA = $suppliedAAfter->first();
        $this->assertEquals(10, (float) $prodA->supplier_stock, 'Supplier A stock was incorrectly modified!');
        $this->assertEquals(15, (float) $prodA->stock_qty, 'Warehouse total stock incorrect!');
        $this->assertCount(1, $prodA->batches);
        $this->assertEquals('LOT-SUP-A', $prodA->batches->first()->batch_number);

        // 6. Assertions on Supplier B
        $suppliedBAfter = $supplierB->getSuppliedProducts();
        $this->assertCount(1, $suppliedBAfter, 'Supplier B does not see the product in supplied products!');
        $prodB = $suppliedBAfter->first();
        // Supplier B only supplied 5 pieces, NOT 15!
        $this->assertEquals(5, (float) $prodB->supplier_stock, 'Supplier B was credited with Supplier A old stock!');
        $this->assertEquals(15, (float) $prodB->stock_qty, 'Warehouse total stock incorrect!');
        $this->assertCount(1, $prodB->batches);
        $this->assertEquals('LOT-SUP-B', $prodB->batches->first()->batch_number);

        // 7. Verify UI views load cleanly
        $resA = $this->actingAs($admin, 'admin')->get("/admin/suppliers/{$supplierA->id}?tab=products");
        $resA->assertStatus(200);
        $resA->assertSee('Gala Apple Tree');
        $resA->assertSee('LOT-SUP-A');

        $resB = $this->actingAs($admin, 'admin')->get("/admin/suppliers/{$supplierB->id}?tab=products");
        $resB->assertStatus(200);
        $resB->assertSee('Gala Apple Tree');
        $resB->assertSee('LOT-SUP-B');

        // 8. Delete Supplier B and ensure Supplier A product is NOT deleted
        $delResponse = $this->actingAs($admin, 'admin')->delete("/admin/suppliers/{$supplierB->id}");
        $delResponse->assertRedirect('/admin/suppliers');

        $product->refresh();
        $this->assertNotNull($product);
        $this->assertEquals($supplierA->id, $product->supplier_id);
        // Stock reverted from 15 to 10 (Supplier B 5 pcs removed)
        $this->assertEquals(10, (float) $product->stock_qty);

        $suppliedAFinal = $supplierA->getSuppliedProducts();
        $this->assertCount(1, $suppliedAFinal);
        $this->assertEquals(10, (float) $suppliedAFinal->first()->supplier_stock);
    }

    public function test_product_can_have_multiple_suppliers_assigned_and_managed(): void
    {
        $admin = $this->actingAdmin();

        $supplier1 = Supplier::create(['name' => 'Kashmir Agro', 'is_active' => true]);
        $supplier2 = Supplier::create(['name' => 'Himachal Plants', 'is_active' => true]);
        $supplier3 = Supplier::create(['name' => 'Punjab Seeds', 'is_active' => true]);

        // 1. Create a product with multiple suppliers
        $createResponse = $this->actingAs($admin, 'admin')->post('/admin/products', [
            'name' => 'Honeycrisp Apple Scion',
            'unit' => 'pcs',
            'rate' => 250,
            'supplier_ids' => [$supplier1->id, $supplier2->id],
            'supplier_id' => $supplier1->id, // primary
            'is_active' => 1,
        ]);
        $createResponse->assertRedirect(route('admin.products.index'));

        $product = Product::where('name', 'Honeycrisp Apple Scion')->first();
        $this->assertNotNull($product);
        $this->assertEquals($supplier1->id, $product->supplier_id);
        $this->assertCount(2, $product->suppliers);
        $this->assertTrue($product->suppliers->contains($supplier1));
        $this->assertTrue($product->suppliers->contains($supplier2));

        // 2. Both suppliers query this product in their supplied list
        $s1Products = $supplier1->getSuppliedProducts();
        $this->assertCount(1, $s1Products);
        $this->assertEquals($product->id, $s1Products->first()->id);

        $s2Products = $supplier2->getSuppliedProducts();
        $this->assertCount(1, $s2Products);
        $this->assertEquals($product->id, $s2Products->first()->id);

        // 3. Edit product to add a 3rd supplier and change primary
        $updateResponse = $this->actingAs($admin, 'admin')->put("/admin/products/{$product->id}", [
            'name' => 'Honeycrisp Apple Scion',
            'unit' => 'pcs',
            'rate' => 260,
            'supplier_ids' => [$supplier1->id, $supplier2->id, $supplier3->id],
            'supplier_id' => $supplier2->id, // change primary to supplier 2
            'is_active' => 1,
        ]);
        $updateResponse->assertRedirect(route('admin.products.index'));

        $product->refresh();
        $this->assertEquals($supplier2->id, $product->supplier_id);
        $this->assertCount(3, $product->suppliers);
        $this->assertTrue($product->suppliers->contains($supplier3));

        // 4. Products index page lists all 3 suppliers
        $indexResponse = $this->actingAs($admin, 'admin')->get('/admin/products');
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Kashmir Agro');
        $indexResponse->assertSee('Himachal Plants');
        $indexResponse->assertSee('Punjab Seeds');

        // 5. Filter by supplier 3 returns this product
        $filterResponse = $this->actingAs($admin, 'admin')->get("/admin/products?supplier_id={$supplier3->id}");
        $filterResponse->assertStatus(200);
        $filterResponse->assertSee('Honeycrisp Apple Scion');
    }
}
