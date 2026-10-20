<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\PosCustomer;
use App\Models\PosSale;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Supplier;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosAndInvoiceSupplierBatchSaleTest extends TestCase
{
    use RefreshDatabase;

    private function actingAdmin(): Admin
    {
        $this->seed(AdminSeeder::class);

        return Admin::where('email', 'admin@pta.com')->first();
    }

    public function test_pos_checkout_with_explicit_supplier_and_batch(): void
    {
        $admin = $this->actingAdmin();

        $supplierA = Supplier::create(['name' => 'Supplier A', 'phone' => '1111111111', 'is_active' => true]);
        $supplierB = Supplier::create(['name' => 'Supplier B', 'phone' => '2222222222', 'is_active' => true]);

        $product = Product::create([
            'name' => 'Apple Tree M9',
            'unit' => 'pcs',
            'rate' => 300,
            'selling_price' => 450,
            'stock_qty' => 30,
            'supplier_id' => $supplierA->id,
            'is_active' => true,
        ]);
        $product->suppliers()->sync([$supplierA->id, $supplierB->id]);

        $batchA = ProductBatch::create([
            'product_id' => $product->id,
            'batch_number' => 'BATCH-A-01',
            'inward_date' => now()->subDays(10)->toDateString(),
            'initial_qty' => 10,
            'current_qty' => 10,
            'unit_cost' => 200,
            'selling_price' => 400,
            'supplier_id' => $supplierA->id,
            'status' => ProductBatch::STATUS_ACTIVE,
        ]);

        $batchB = ProductBatch::create([
            'product_id' => $product->id,
            'batch_number' => 'BATCH-B-01',
            'inward_date' => now()->subDays(5)->toDateString(),
            'initial_qty' => 20,
            'current_qty' => 20,
            'unit_cost' => 250,
            'selling_price' => 480,
            'supplier_id' => $supplierB->id,
            'status' => ProductBatch::STATUS_ACTIVE,
        ]);

        $customer = PosCustomer::create(['name' => 'John Orchardist', 'phone' => '9998887776']);

        // Cashier explicitly picks Supplier B and Batch B in POS
        $response = $this->actingAs($admin, 'admin')->postJson(route('pos.checkout'), [
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'payment_method' => 'cash',
            'amount_paid' => 1440,
            'amount_tendered' => 1440,
            'items' => [
                [
                    'product_id' => $product->id,
                    'supplier_id' => $supplierB->id,
                    'batch_id' => $batchB->id,
                    'quantity' => 3,
                    'unit_price' => 480,
                    'discount' => 0,
                ],
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        // Verify batch quantities: Batch B reduced by 3, Batch A remains 10!
        $this->assertEquals(17.0, (float) $batchB->fresh()->current_qty);
        $this->assertEquals(10.0, (float) $batchA->fresh()->current_qty);
        $this->assertEquals(27.0, (float) $product->fresh()->stock_qty);

        // Verify PosSaleItem record
        $sale = PosSale::latest('id')->first();
        $this->assertNotNull($sale);
        $item = $sale->items()->first();
        $this->assertEquals($batchB->id, $item->batch_id);
        $this->assertEquals($supplierB->id, $item->supplier_id);
        $this->assertEquals(250.0, (float) $item->cost_price);
        $this->assertEquals(480.0, (float) $item->unit_price);
        $this->assertEquals(3.0, (float) $item->quantity);
    }

    public function test_pos_checkout_with_supplier_only_scopes_fifo_to_that_supplier_batches(): void
    {
        $admin = $this->actingAdmin();

        $supplierA = Supplier::create(['name' => 'Supplier Alpha', 'phone' => '1111111112', 'is_active' => true]);
        $supplierB = Supplier::create(['name' => 'Supplier Beta', 'phone' => '2222222223', 'is_active' => true]);

        $product = Product::create([
            'name' => 'Golden Delicious Tree',
            'unit' => 'pcs',
            'rate' => 300,
            'selling_price' => 500,
            'stock_qty' => 20,
            'supplier_id' => $supplierA->id,
            'is_active' => true,
        ]);
        $product->suppliers()->sync([$supplierA->id, $supplierB->id]);

        // Older batch belongs to Supplier A
        $batchA = ProductBatch::create([
            'product_id' => $product->id,
            'batch_number' => 'ALPHA-OLD-LOT',
            'inward_date' => now()->subDays(20)->toDateString(),
            'initial_qty' => 10,
            'current_qty' => 10,
            'unit_cost' => 200,
            'selling_price' => 450,
            'supplier_id' => $supplierA->id,
            'status' => ProductBatch::STATUS_ACTIVE,
        ]);

        // Newer batch belongs to Supplier B
        $batchB = ProductBatch::create([
            'product_id' => $product->id,
            'batch_number' => 'BETA-NEW-LOT',
            'inward_date' => now()->subDays(5)->toDateString(),
            'initial_qty' => 10,
            'current_qty' => 10,
            'unit_cost' => 260,
            'selling_price' => 520,
            'supplier_id' => $supplierB->id,
            'status' => ProductBatch::STATUS_ACTIVE,
        ]);

        $customer = PosCustomer::create(['name' => 'Jane Orchardist', 'phone' => '9998887775']);

        // Cashier selects Supplier Beta without selecting a batch_id
        $response = $this->actingAs($admin, 'admin')->postJson(route('pos.checkout'), [
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'payment_method' => 'cash',
            'amount_paid' => 1500,
            'amount_tendered' => 1500,
            'items' => [
                [
                    'product_id' => $product->id,
                    'supplier_id' => $supplierB->id,
                    'batch_id' => null,
                    'quantity' => 4,
                    'unit_price' => 500,
                    'discount' => 0,
                ],
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        // Even though Batch A is older, FIFO scoped to Supplier B deducted from Batch B!
        $this->assertEquals(6.0, (float) $batchB->fresh()->current_qty);
        $this->assertEquals(10.0, (float) $batchA->fresh()->current_qty);
        $this->assertEquals(16.0, (float) $product->fresh()->stock_qty);

        $sale = PosSale::latest('id')->first();
        $item = $sale->items()->first();
        $this->assertEquals($batchB->id, $item->batch_id);
        $this->assertEquals($supplierB->id, $item->supplier_id);
    }

    public function test_invoice_creation_with_explicit_supplier_and_batch_and_cancellation(): void
    {
        $admin = $this->actingAdmin();

        $supplierA = Supplier::create(['name' => 'Supplier A', 'phone' => '1111111111', 'is_active' => true]);
        $supplierB = Supplier::create(['name' => 'Supplier B', 'phone' => '2222222222', 'is_active' => true]);

        $product = Product::create([
            'name' => 'Cherry Rootstock',
            'unit' => 'pcs',
            'rate' => 200,
            'selling_price' => 350,
            'stock_qty' => 25,
            'supplier_id' => $supplierA->id,
            'is_active' => true,
        ]);
        $product->suppliers()->sync([$supplierA->id, $supplierB->id]);

        $batchA = ProductBatch::create([
            'product_id' => $product->id,
            'batch_number' => 'CHERRY-A-01',
            'inward_date' => now()->subDays(8)->toDateString(),
            'initial_qty' => 10,
            'current_qty' => 10,
            'unit_cost' => 150,
            'selling_price' => 300,
            'supplier_id' => $supplierA->id,
            'status' => ProductBatch::STATUS_ACTIVE,
        ]);

        $batchB = ProductBatch::create([
            'product_id' => $product->id,
            'batch_number' => 'CHERRY-B-01',
            'inward_date' => now()->subDays(3)->toDateString(),
            'initial_qty' => 15,
            'current_qty' => 15,
            'unit_cost' => 180,
            'selling_price' => 360,
            'supplier_id' => $supplierB->id,
            'status' => ProductBatch::STATUS_ACTIVE,
        ]);

        $customer = Customer::create([
            'name' => 'Orchard Customer',
            'phone' => '9988776655',
            'password' => bcrypt('secret123'),
            'status' => 'active',
        ]);

        // Create Manual Invoice selecting Supplier B and Batch B
        $response = $this->actingAs($admin, 'admin')->post(route('admin.invoices.store'), [
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $product->id,
                    'supplier_id' => $supplierB->id,
                    'batch_id' => $batchB->id,
                    'name' => $product->name,
                    'unit' => 'pcs',
                    'qty' => 5,
                    'rate' => 360,
                    'discount' => 0,
                    'gst_rate' => 18,
                ],
            ],
        ]);

        $invoice = Invoice::latest('id')->first();
        $this->assertNotNull($invoice);
        $response->assertRedirect(route('admin.invoices.show', $invoice));

        // Verify stock deducted specifically from Batch B
        $this->assertEquals(10.0, (float) $batchB->fresh()->current_qty);
        $this->assertEquals(10.0, (float) $batchA->fresh()->current_qty);
        $this->assertEquals(20.0, (float) $product->fresh()->stock_qty);

        // Verify InvoiceItem has both supplier_id and batch_id
        $invItem = $invoice->items()->first();
        $this->assertEquals($batchB->id, $invItem->batch_id);
        $this->assertEquals($supplierB->id, $invItem->supplier_id);
        $this->assertEquals(5.0, (float) $invItem->qty);

        // Cancel Invoice and verify stock reversal back to Batch B
        $cancelResponse = $this->actingAs($admin, 'admin')->patch(route('admin.invoices.cancel', $invoice));
        $cancelResponse->assertRedirect(route('admin.invoices.show', $invoice));

        $this->assertEquals(15.0, (float) $batchB->fresh()->current_qty);
        $this->assertEquals(10.0, (float) $batchA->fresh()->current_qty);
        $this->assertEquals(25.0, (float) $product->fresh()->stock_qty);
        $this->assertEquals('cancelled', $invoice->fresh()->status);
    }
}
