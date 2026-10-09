<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\PurchaseBill;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseBillTest extends TestCase
{
    use RefreshDatabase;

    private function actingAdmin(): Admin
    {
        $this->seed(AdminSeeder::class);

        return Admin::where('email', 'admin@pta.com')->first();
    }

    public function test_purchase_bill_create_screen_loads(): void
    {
        $admin = $this->actingAdmin();

        $supplier = Supplier::create([
            'name' => 'Agro Seeds India',
            'phone' => '9876543210',
            'is_active' => true,
        ]);

        $product = Product::create([
            'name' => 'Gala Apple Rootstock',
            'unit' => 'pcs',
            'rate' => 300,
            'gst_rate' => 12,
            'stock_qty' => 50,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')->get('/admin/purchase-bills/create');
        $response->assertStatus(200);
        $response->assertSee('Record Purchase Bill');
        $response->assertSee('Gala Apple Rootstock');
        $response->assertSee('Agro Seeds India');
    }

    public function test_purchase_bill_store_updates_stock_and_creates_batch(): void
    {
        $admin = $this->actingAdmin();

        $supplier = Supplier::create([
            'name' => 'Kashmir Nursery Corp',
            'phone' => '9876543211',
            'is_active' => true,
        ]);

        $product = Product::create([
            'name' => 'M9 Rootstock',
            'unit' => 'pcs',
            'rate' => 400,
            'gst_rate' => 18,
            'stock_qty' => 10,
            'is_active' => true,
        ]);

        $billData = [
            'supplier_id' => $supplier->id,
            'supplier_invoice_no' => 'INV-2026-001',
            'bill_date' => now()->format('Y-m-d'),
            'discount' => 100,
            'shipping_cost' => 50,
            'initial_payment' => '1',
            'initial_payment_amount' => 500,
            'initial_payment_method' => 'bank_transfer',
            'initial_payment_reference' => 'UTR123456',
            'items' => [
                [
                    'product_id' => $product->id,
                    'batch_number' => 'LOT-KMR-01',
                    'mfg_date' => now()->subMonth()->format('Y-m-d'),
                    'expiry_date' => now()->addYear()->format('Y-m-d'),
                    'quantity' => 20,
                    'unit_cost' => 250,
                    'selling_price' => 420,
                    'tax_percent' => 18,
                ]
            ],
        ];

        $response = $this->actingAs($admin, 'admin')->post('/admin/purchase-bills', $billData);
        $response->assertRedirect();

        $bill = PurchaseBill::where('supplier_id', $supplier->id)->first();
        $this->assertNotNull($bill);
        $this->assertEquals('INV-2026-001', $bill->supplier_invoice_no);
        $this->assertEquals('partial', $bill->payment_status);
        $this->assertEquals(500, (float) $bill->paid_amount);

        // Product stock incremented from 10 to 30
        $product->refresh();
        $this->assertEquals(30, (float) $product->stock_qty);
        $this->assertEquals(420, (float) $product->rate);

        // Batch created
        $batch = ProductBatch::where('batch_number', 'LOT-KMR-01')->first();
        $this->assertNotNull($batch);
        $this->assertEquals(20, (float) $batch->current_qty);
        $this->assertEquals(250, (float) $batch->unit_cost);

        // Stock movement recorded
        $movement = StockMovement::where('purchase_bill_id', $bill->id)->first();
        $this->assertNotNull($movement);
        $this->assertEquals('in', $movement->type);
        $this->assertEquals(20, (float) $movement->quantity);
        $this->assertEquals(30, (float) $movement->stock_after);

        // Supplier ledger balance
        $supplier->refresh();
        $this->assertEquals((float) $bill->total_amount, (float) $supplier->total_purchased);
        $this->assertEquals(500, (float) $supplier->total_paid);
        $this->assertEquals((float) $bill->balance_due, (float) $supplier->balance_due);

        // Show page loads
        $showResponse = $this->actingAs($admin, 'admin')->get("/admin/purchase-bills/{$bill->id}");
        $showResponse->assertStatus(200);
        $showResponse->assertSee($bill->bill_number);

        // Supplier show page with tabs loads
        $supplierShowResponse = $this->actingAs($admin, 'admin')->get("/admin/suppliers/{$supplier->id}");
        $supplierShowResponse->assertStatus(200);
        $supplierShowResponse->assertSee($supplier->name);
        $supplierShowResponse->assertSee($bill->bill_number);
    }

    public function test_purchase_bill_store_without_initial_payment_succeeds(): void
    {
        $admin = $this->actingAdmin();

        $supplier = Supplier::create([
            'name' => 'Himachal Orchards Supply',
            'phone' => '9876543299',
            'is_active' => true,
        ]);

        $product = Product::create([
            'name' => 'Fuji Apple Scion',
            'unit' => 'pcs',
            'rate' => 200,
            'stock_qty' => 5,
            'is_active' => true,
        ]);

        // Simulating form submission with toggle off and initial_payment_amount sent as 0 / empty
        $billData = [
            'supplier_id' => $supplier->id,
            'bill_date' => now()->format('Y-m-d'),
            'discount' => 0,
            'shipping_cost' => 0,
            'initial_payment' => null,
            'initial_payment_amount' => 0,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 10,
                    'unit_cost' => 150,
                    'tax_percent' => 0,
                ]
            ],
        ];

        $response = $this->actingAs($admin, 'admin')->post('/admin/purchase-bills', $billData);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $bill = PurchaseBill::where('supplier_id', $supplier->id)->first();
        $this->assertNotNull($bill);
        $this->assertEquals('unpaid', $bill->payment_status);
        $this->assertEquals(0, (float) $bill->paid_amount);
        $this->assertEquals(1500, (float) $bill->balance_due);
    }

    public function test_can_delete_supplier_payment_from_purchase_bill(): void
    {
        $admin = $this->actingAdmin();

        $supplier = Supplier::create([
            'name' => 'Kashmir Nursery Corp',
            'phone' => '9876543211',
            'is_active' => true,
        ]);

        $bill = PurchaseBill::create([
            'bill_number' => 'PB-2026-0001',
            'supplier_id' => $supplier->id,
            'bill_date' => now()->format('Y-m-d'),
            'subtotal' => 2000,
            'tax_amount' => 0,
            'discount' => 0,
            'shipping_cost' => 0,
            'total_amount' => 2000,
            'paid_amount' => 1000,
            'balance_due' => 1000,
            'payment_status' => 'partial',
            'status' => 'received',
            'created_by' => $admin->id,
        ]);

        $payment = $bill->payments()->create([
            'payment_number' => 'SPAY-2026-0001',
            'supplier_id' => $supplier->id,
            'amount' => 1000,
            'payment_date' => now()->format('Y-m-d'),
            'payment_method' => 'bank_transfer',
            'reference_no' => 'UTR998877',
            'created_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('supplier_payments', ['id' => $payment->id]);

        $response = $this->actingAs($admin, 'admin')
            ->delete("/admin/purchase-bills/{$bill->id}/payments/{$payment->id}");

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('supplier_payments', ['id' => $payment->id]);

        $bill->refresh();
        $this->assertEquals(0, (float) $bill->paid_amount);
        $this->assertEquals(2000, (float) $bill->balance_due);
        $this->assertEquals('unpaid', $bill->payment_status);
    }

    public function test_can_delete_supplier_payment_from_supplier_profile(): void
    {
        $admin = $this->actingAdmin();

        $supplier = Supplier::create([
            'name' => 'Agro Inputs Direct',
            'phone' => '9876543200',
            'is_active' => true,
        ]);

        $bill = PurchaseBill::create([
            'bill_number' => 'PB-2026-0002',
            'supplier_id' => $supplier->id,
            'bill_date' => now()->format('Y-m-d'),
            'subtotal' => 5000,
            'tax_amount' => 0,
            'discount' => 0,
            'shipping_cost' => 0,
            'total_amount' => 5000,
            'paid_amount' => 5000,
            'balance_due' => 0,
            'payment_status' => 'paid',
            'status' => 'received',
            'created_by' => $admin->id,
        ]);

        $payment = SupplierPayment::create([
            'payment_number' => 'SPAY-2026-0002',
            'supplier_id' => $supplier->id,
            'purchase_bill_id' => $bill->id,
            'amount' => 5000,
            'payment_date' => now()->format('Y-m-d'),
            'payment_method' => 'upi',
            'created_by' => $admin->id,
        ]);

        $this->assertEquals(5000, $supplier->total_paid);

        $response = $this->actingAs($admin, 'admin')
            ->delete("/admin/suppliers/{$supplier->id}/payments/{$payment->id}");

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('supplier_payments', ['id' => $payment->id]);

        $bill->refresh();
        $this->assertEquals(0, (float) $bill->paid_amount);
        $this->assertEquals(5000, (float) $bill->balance_due);
        $this->assertEquals('unpaid', $bill->payment_status);

        $supplier->refresh();
        $this->assertEquals(0, $supplier->total_paid);
        $this->assertEquals(5000, $supplier->balance_due);
    }

    public function test_can_delete_supplier_with_cascading_stock_reversal_and_cleanup(): void
    {
        $admin = $this->actingAdmin();

        $supplier = Supplier::create([
            'name' => 'Valley Orchard Equipment & Seeds',
            'phone' => '9876500000',
            'is_active' => true,
        ]);

        // Product belonging to this supplier (should be deleted when supplier is deleted)
        $product = Product::create([
            'name' => 'Golden Delicious Sapling',
            'unit' => 'pcs',
            'rate' => 350,
            'stock_qty' => 10,
            'supplier_id' => $supplier->id,
            'is_active' => true,
        ]);

        // External product belonging to catalog/no supplier (stock should be reversed, product kept)
        $externalProduct = Product::create([
            'name' => 'General Pruning Shears',
            'unit' => 'pcs',
            'rate' => 500,
            'stock_qty' => 5,
            'supplier_id' => null,
            'is_active' => true,
        ]);

        // Create purchase bill that increases stock of $product by 25 and $externalProduct by 10
        $bill = PurchaseBill::create([
            'bill_number' => 'PB-2026-9999',
            'supplier_id' => $supplier->id,
            'bill_date' => now()->format('Y-m-d'),
            'subtotal' => 10000,
            'tax_amount' => 0,
            'discount' => 0,
            'shipping_cost' => 0,
            'total_amount' => 10000,
            'paid_amount' => 2000,
            'balance_due' => 8000,
            'payment_status' => 'partial',
            'status' => 'received',
            'created_by' => $admin->id,
        ]);

        $batch = ProductBatch::create([
            'product_id' => $product->id,
            'batch_number' => 'LOT-VALLEY-01',
            'inward_date' => now()->format('Y-m-d'),
            'initial_qty' => 25,
            'current_qty' => 25,
            'unit_cost' => 250,
            'selling_price' => 350,
            'supplier_id' => $supplier->id,
            'status' => ProductBatch::STATUS_ACTIVE,
        ]);

        $item1 = $bill->items()->create([
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'batch_number' => 'LOT-VALLEY-01',
            'quantity' => 25,
            'unit_cost' => 250,
            'selling_price' => 350,
            'tax_percent' => 0,
            'tax_amount' => 0,
            'line_total' => 6250,
        ]);

        $item2 = $bill->items()->create([
            'product_id' => $externalProduct->id,
            'quantity' => 10,
            'unit_cost' => 375,
            'selling_price' => 500,
            'tax_percent' => 0,
            'tax_amount' => 0,
            'line_total' => 3750,
        ]);

        // Reflect inward stock additions
        $product->update(['stock_qty' => 35]);
        $externalProduct->update(['stock_qty' => 15]);

        $movement = StockMovement::create([
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'type' => 'in',
            'quantity' => 25,
            'stock_after' => 35,
            'unit_cost' => 250,
            'supplier_id' => $supplier->id,
            'reference' => 'Purchase Bill ' . $bill->bill_number,
            'created_by' => $admin->id,
        ]);

        $payment = SupplierPayment::create([
            'payment_number' => 'SPAY-2026-9999',
            'supplier_id' => $supplier->id,
            'purchase_bill_id' => $bill->id,
            'amount' => 2000,
            'payment_date' => now()->format('Y-m-d'),
            'payment_method' => 'cash',
            'created_by' => $admin->id,
        ]);

        $this->assertEquals(35, (float) $product->stock_qty);
        $this->assertEquals(15, (float) $externalProduct->stock_qty);
        $this->assertEquals($supplier->id, $product->supplier_id);
        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id]);
        $this->assertDatabaseHas('products', ['id' => $product->id]);
        $this->assertDatabaseHas('products', ['id' => $externalProduct->id]);
        $this->assertDatabaseHas('purchase_bills', ['id' => $bill->id]);
        $this->assertDatabaseHas('purchase_bill_items', ['id' => $item1->id]);
        $this->assertDatabaseHas('purchase_bill_items', ['id' => $item2->id]);
        $this->assertDatabaseHas('product_batches', ['id' => $batch->id]);
        $this->assertDatabaseHas('stock_movements', ['id' => $movement->id]);
        $this->assertDatabaseHas('supplier_payments', ['id' => $payment->id]);

        // Perform deletion of supplier
        $response = $this->actingAs($admin, 'admin')
            ->delete("/admin/suppliers/{$supplier->id}");

        $response->assertRedirect('/admin/suppliers');
        $response->assertSessionHas('success');

        // Products of the supplier are permanently deleted
        $this->assertDatabaseMissing('products', ['id' => $product->id]);

        // External product remains in catalog, but stock added from this bill is reversed back from 15 to 5
        $this->assertDatabaseHas('products', ['id' => $externalProduct->id]);
        $externalProduct->refresh();
        $this->assertEquals(5, (float) $externalProduct->stock_qty);

        // Supplier, purchase bills, batches, stock movements, and payments deleted
        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
        $this->assertDatabaseMissing('purchase_bills', ['id' => $bill->id]);
        $this->assertDatabaseMissing('purchase_bill_items', ['id' => $item1->id]);
        $this->assertDatabaseMissing('purchase_bill_items', ['id' => $item2->id]);
        $this->assertDatabaseMissing('product_batches', ['id' => $batch->id]);
        $this->assertDatabaseMissing('stock_movements', ['id' => $movement->id]);
        $this->assertDatabaseMissing('supplier_payments', ['id' => $payment->id]);
    }

    public function test_purchase_bill_create_screen_supports_inline_quick_product_creation(): void
    {
        $admin = $this->actingAdmin();

        $supplier = Supplier::create([
            'name' => 'Kashmir Agro Bio Corp',
            'phone' => '9876599999',
            'is_active' => true,
        ]);

        // Screen loads with Quick Add button and modal
        $response = $this->actingAs($admin, 'admin')->get("/admin/purchase-bills/create?supplier_id={$supplier->id}");
        $response->assertStatus(200);
        $response->assertSee('Quick Add Product');
        $response->assertSee('Add New Product');

        // Inline product AJAX creation endpoint
        $createResponse = $this->actingAs($admin, 'admin')
            ->postJson('/admin/products', [
                'name' => 'Red Chief Apple Feathered Tree',
                'sku' => 'RC-TREE-01',
                'unit' => 'pcs',
                'type' => 'material',
                'rate' => 380,
                'selling_price' => 550,
                'gst_rate' => 12,
                'supplier_id' => $supplier->id,
            ]);

        $createResponse->assertStatus(201);
        $createResponse->assertJson([
            'success' => true,
            'product' => [
                'name' => 'Red Chief Apple Feathered Tree',
                'sku' => 'RC-TREE-01',
                'unit' => 'pcs',
                'rate' => 380,
                'selling_price' => 550,
                'gst_rate' => 12,
            ],
        ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Red Chief Apple Feathered Tree',
            'sku' => 'RC-TREE-01',
            'supplier_id' => $supplier->id,
            'rate' => 380,
        ]);

        // Validation returns 422 JSON for invalid inputs
        $invalidResponse = $this->actingAs($admin, 'admin')
            ->postJson('/admin/products', [
                'name' => '',
                'rate' => -5,
            ]);

        $invalidResponse->assertStatus(422);
        $invalidResponse->assertJsonValidationErrors(['name', 'rate', 'unit']);
    }
}


