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
}
