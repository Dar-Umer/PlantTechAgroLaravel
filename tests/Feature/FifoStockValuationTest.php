<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Supplier;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FifoStockValuationTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private Supplier $supplier;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Super Admin', 'guard_name' => 'admin']);
        $this->admin = Admin::create([
            'name' => 'Store Manager',
            'email' => 'manager@test.com',
            'password' => bcrypt('password'),
            'phone' => '9999999999',
            'role' => 'admin',
            'is_active' => true,
        ]);
        $this->admin->assignRole('Super Admin');

        $this->supplier = Supplier::create([
            'name' => 'Agro Chemicals Pvt Ltd',
            'contact_person' => 'Rajesh Sharma',
            'phone' => '9876543210',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'name' => 'High Density Apple Rootstock',
            'sku' => 'HD-ROOT-01',
            'type' => 'sellable',
            'unit' => 'pcs',
            'stock_qty' => 0,
            'rate' => 100.00,
            'selling_price' => 150.00,
            'is_active' => true,
        ]);
    }

    public function test_multi_rate_supplier_inward_and_fifo_deduction(): void
    {
        // 1. Supplier sends 10 items with rate 100
        $mv1 = StockService::record(
            product: $this->product,
            type: 'in',
            quantity: 10,
            reference: 'PO-001',
            supplierId: $this->supplier->id,
            unitCost: 100.00,
            batchNumber: 'BATCH-INITIAL'
        );

        $this->product->refresh();
        $this->assertEquals(10.0, (float) $this->product->stock_qty);

        $batch1 = ProductBatch::where('product_id', $this->product->id)->first();
        $this->assertNotNull($batch1);
        $this->assertEquals(10.0, (float) $batch1->current_qty);
        $this->assertEquals(100.00, (float) $batch1->unit_cost);

        // 2. Sell 1 item from these 10 with the base rate of 100 -> now 9 are left
        StockService::record(
            product: $this->product,
            type: 'out',
            quantity: 1,
            reference: 'SALE-001'
        );

        $this->product->refresh();
        $batch1->refresh();
        $this->assertEquals(9.0, (float) $this->product->stock_qty);
        $this->assertEquals(9.0, (float) $batch1->current_qty);
        $this->assertEquals(100.00, (float) $batch1->unit_cost);

        // 3. Supplier sends more 10 with rate increase (rate is now 110)
        StockService::record(
            product: $this->product,
            type: 'in',
            quantity: 10,
            reference: 'PO-002',
            supplierId: $this->supplier->id,
            unitCost: 110.00,
            batchNumber: 'BATCH-RATE-HIKE'
        );

        $this->product->refresh();
        $this->assertEquals(19.0, (float) $this->product->stock_qty);

        // 4. Verify: The 9 which were left still have the old rate (100) and new stock has new rate (110)
        $batch1->refresh();
        $batch2 = ProductBatch::where('product_id', $this->product->id)
            ->where('batch_number', 'BATCH-RATE-HIKE')
            ->first();

        $this->assertNotNull($batch2);
        $this->assertEquals(9.0, (float) $batch1->current_qty);
        $this->assertEquals(100.00, (float) $batch1->unit_cost);

        $this->assertEquals(10.0, (float) $batch2->current_qty);
        $this->assertEquals(110.00, (float) $batch2->unit_cost);

        // Total inventory valuation = (9 * 100) + (10 * 110) = 900 + 1100 = 2000
        $this->assertEquals(2000.00, $this->product->stockValuation());
        $this->assertEquals(round(2000 / 19, 2), $this->product->weightedAverageCost());

        // 5. Sell 12 items via FIFO:
        // Consumes all 9 from Batch 1 (@ 100), and 3 from Batch 2 (@ 110)
        StockService::record(
            product: $this->product,
            type: 'out',
            quantity: 12,
            reference: 'SALE-BULK'
        );

        $this->product->refresh();
        $batch1->refresh();
        $batch2->refresh();

        $this->assertEquals(7.0, (float) $this->product->stock_qty);
        $this->assertEquals(0.0, (float) $batch1->current_qty);
        $this->assertEquals(ProductBatch::STATUS_DEPLETED, $batch1->status);

        $this->assertEquals(7.0, (float) $batch2->current_qty);
        $this->assertEquals(110.00, (float) $batch2->unit_cost);
        $this->assertEquals(ProductBatch::STATUS_ACTIVE, $batch2->status);

        // Remaining valuation: 7 * 110 = 770
        $this->assertEquals(770.00, $this->product->stockValuation());
    }
}
