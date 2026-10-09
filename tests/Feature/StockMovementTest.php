<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockMovement;
use App\Models\Supplier;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockMovementTest extends TestCase
{
    use RefreshDatabase;

    private function actingAdmin(): Admin
    {
        $this->seed(AdminSeeder::class);

        return Admin::where('email', 'admin@pta.com')->first();
    }

    public function test_stock_movements_index_renders_with_actions(): void
    {
        $admin = $this->actingAdmin();

        $product = Product::create([
            'name' => 'Fuji Apple Sapling',
            'unit' => 'pcs',
            'rate' => 250,
            'stock_qty' => 30,
            'is_active' => true,
        ]);

        $movement = StockMovement::create([
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 10,
            'stock_after' => 30,
            'reference' => 'MV-TEST-001',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin, 'admin')->get('/admin/stock-movements');
        $response->assertStatus(200);
        $response->assertSee('MV-TEST-001');
        $response->assertSee('Delete Stock Movement');
    }

    public function test_can_delete_inward_stock_movement_and_reverse_inventory(): void
    {
        $admin = $this->actingAdmin();

        $product = Product::create([
            'name' => 'Kala Zeera Seed',
            'unit' => 'kg',
            'rate' => 1200,
            'stock_qty' => 60,
            'is_active' => true,
        ]);

        $batch = ProductBatch::create([
            'product_id' => $product->id,
            'batch_number' => 'LOT-KZ-01',
            'inward_date' => now()->format('Y-m-d'),
            'initial_qty' => 10,
            'current_qty' => 10,
            'unit_cost' => 800,
            'status' => ProductBatch::STATUS_ACTIVE,
        ]);

        $movement = StockMovement::create([
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'type' => 'in',
            'quantity' => 10,
            'stock_after' => 60,
            'reference' => 'MV-INWARD-001',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->delete("/admin/stock-movements/{$movement->id}");

        $response->assertRedirect('/admin/stock-movements');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('stock_movements', ['id' => $movement->id]);

        $product->refresh();
        $this->assertEquals(50, (float) $product->stock_qty);

        $batch->refresh();
        $this->assertEquals(0, (float) $batch->current_qty);
    }

    public function test_can_delete_outward_stock_movement_and_restore_inventory(): void
    {
        $admin = $this->actingAdmin();

        $product = Product::create([
            'name' => 'Copper Fungicide 500g',
            'unit' => 'pack',
            'rate' => 350,
            'stock_qty' => 40,
            'is_active' => true,
        ]);

        $batch = ProductBatch::create([
            'product_id' => $product->id,
            'batch_number' => 'LOT-CF-01',
            'inward_date' => now()->format('Y-m-d'),
            'initial_qty' => 25,
            'current_qty' => 15,
            'unit_cost' => 200,
            'status' => ProductBatch::STATUS_ACTIVE,
        ]);

        $movement = StockMovement::create([
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'type' => 'out',
            'quantity' => -10,
            'stock_after' => 40,
            'reference' => 'MV-OUT-001',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->delete("/admin/stock-movements/{$movement->id}");

        $response->assertRedirect('/admin/stock-movements');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('stock_movements', ['id' => $movement->id]);

        $product->refresh();
        $this->assertEquals(50, (float) $product->stock_qty);

        $batch->refresh();
        $this->assertEquals(25, (float) $batch->current_qty);
    }

    public function test_can_delete_adjustment_stock_movement_and_restore_inventory(): void
    {
        $admin = $this->actingAdmin();

        $product = Product::create([
            'name' => 'Gala Rootstock Nursery',
            'unit' => 'pcs',
            'rate' => 180,
            'stock_qty' => 45,
            'is_active' => true,
        ]);

        // Adjustment from 30 to 45 (quantity delta = +15)
        $movement = StockMovement::create([
            'product_id' => $product->id,
            'type' => 'adjustment',
            'quantity' => 15,
            'stock_after' => 45,
            'reference' => 'MV-ADJ-001',
            'note' => 'Physical count correction',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->delete("/admin/stock-movements/{$movement->id}");

        $response->assertRedirect('/admin/stock-movements');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('stock_movements', ['id' => $movement->id]);

        $product->refresh();
        $this->assertEquals(30, (float) $product->stock_qty);
    }
}
