<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductBatchTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Super Admin', 'guard_name' => 'admin']);
        $this->admin = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'phone' => '9999999999',
            'role' => 'admin',
            'is_active' => true,
        ]);
        $this->admin->assignRole('Super Admin');

        $this->product = Product::create([
            'name' => 'Neem Oil Bio-Pesticide',
            'sku' => 'NEEM-001',
            'type' => 'material',
            'unit' => 'litre',
            'stock_quantity' => 0,
            'cost_price' => 250.00,
            'selling_price' => 380.00,
            'hsn_code' => '38089910',
            'is_active' => true,
        ]);
    }

    public function test_stock_in_creates_new_product_batch(): void
    {
        $movement = StockService::record(
            product: $this->product,
            type: 'in',
            quantity: 50.0,
            reference: 'PO-2026-001',
            batchNumber: 'LOT-NM-2026-A',
            mfgDate: now()->subMonth()->toDateString(),
            expiryDate: now()->addYear()->toDateString(),
        );

        $this->assertNotNull($movement->batch_id);

        $batch = ProductBatch::find($movement->batch_id);
        $this->assertNotNull($batch);
        $this->assertEquals('LOT-NM-2026-A', $batch->batch_number);
        $this->assertEquals(50.0, $batch->initial_qty);
        $this->assertEquals(50.0, $batch->current_qty);
        $this->assertEquals(ProductBatch::STATUS_ACTIVE, $batch->status);
    }

    public function test_stock_out_deducts_from_batch(): void
    {
        // Stock in 100 units
        StockService::record(
            product: $this->product,
            type: 'in',
            quantity: 100.0,
            batchNumber: 'LOT-NM-100',
            expiryDate: now()->addMonths(6)->toDateString(),
        );

        $batch = ProductBatch::where('batch_number', 'LOT-NM-100')->first();
        $this->assertEquals(100.0, $batch->current_qty);

        // Deduct 40 units
        StockService::record(
            product: $this->product,
            type: 'out',
            quantity: 40.0,
            batchId: $batch->id,
        );

        $batch->refresh();
        $this->assertEquals(60.0, $batch->current_qty);
        $this->assertEquals(ProductBatch::STATUS_ACTIVE, $batch->status);

        // Deduct remaining 60 units (depletion)
        StockService::record(
            product: $this->product,
            type: 'out',
            quantity: 60.0,
            batchId: $batch->id,
        );

        $batch->refresh();
        $this->assertEquals(0.0, $batch->current_qty);
        $this->assertEquals(ProductBatch::STATUS_DEPLETED, $batch->status);
    }

    public function test_batch_near_expiry_and_expired_status_computation(): void
    {
        $nearExpiryBatch = ProductBatch::create([
            'product_id' => $this->product->id,
            'batch_number' => 'LOT-EXP-SOON',
            'initial_qty' => 10,
            'current_qty' => 10,
            'expiry_date' => now()->addDays(20)->toDateString(),
            'status' => ProductBatch::STATUS_ACTIVE,
        ]);

        $nearExpiryBatch->refreshStatus();
        $this->assertEquals(ProductBatch::STATUS_NEAR_EXPIRY, $nearExpiryBatch->status);
        $this->assertTrue($nearExpiryBatch->isNearExpiry());

        $expiredBatch = ProductBatch::create([
            'product_id' => $this->product->id,
            'batch_number' => 'LOT-ALREADY-EXP',
            'initial_qty' => 10,
            'current_qty' => 10,
            'expiry_date' => now()->subDay()->toDateString(),
            'status' => ProductBatch::STATUS_ACTIVE,
        ]);

        $expiredBatch->refreshStatus();
        $this->assertEquals(ProductBatch::STATUS_EXPIRED, $expiredBatch->status);
        $this->assertTrue($expiredBatch->isExpired());
    }

    public function test_admin_batches_index_and_csv_export(): void
    {
        ProductBatch::create([
            'product_id' => $this->product->id,
            'batch_number' => 'LOT-TEST-VIEW',
            'initial_qty' => 20,
            'current_qty' => 15,
            'expiry_date' => now()->addMonths(3)->toDateString(),
            'status' => ProductBatch::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.product-batches.index'));
        $response->assertOk();
        $response->assertSee('LOT-TEST-VIEW');

        $export = $this->actingAs($this->admin, 'admin')->get(route('admin.product-batches.export'));
        $export->assertOk();
        $export->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
