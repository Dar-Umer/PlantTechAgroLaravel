<?php

namespace Tests\Feature;

use App\Mail\SupplierLowStockMail;
use App\Models\Admin;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Services\StockService;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    private function actingAdmin(): Admin
    {
        $this->seed(AdminSeeder::class);

        return Admin::where('email', 'admin@pta.com')->first();
    }

    public function test_product_creation_with_opening_stock_creates_movement(): void
    {
        $admin = $this->actingAdmin();

        $response = $this->actingAs($admin, 'admin')->post('/admin/products', [
            'name' => 'M9-T337 Apple Plant',
            'unit' => 'pcs',
            'rate' => 350,
            'gst_rate' => 12,
            'stock_qty' => 100,
            'low_stock_threshold' => 20,
            'is_active' => '1',
        ]);

        $product = Product::where('name', 'M9-T337 Apple Plant')->first();
        $this->assertNotNull($product);
        $this->assertEquals('100.000', $product->stock_qty);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 100,
            'reference' => 'OPENING',
        ]);

        $response->assertRedirect(route('admin.products.index'));
    }

    public function test_stock_out_reduces_stock_and_blocks_insufficient(): void
    {
        $product = Product::create([
            'name' => 'Drip Pipe', 'unit' => 'mtr', 'rate' => 25,
            'stock_qty' => 0, 'is_active' => true,
        ]);

        StockService::record($product, 'in', 50);

        $product->refresh();
        $this->assertEquals('50.000', $product->stock_qty);

        $this->expectException(ValidationException::class);
        StockService::record($product, 'out', 100);
    }

    public function test_adjustment_sets_stock_level(): void
    {
        $product = Product::create([
            'name' => 'Trellis Post', 'unit' => 'pcs', 'rate' => 480,
            'stock_qty' => 30, 'is_active' => true,
        ]);

        StockService::record($product, 'adjustment', 12, null, 'Physical count correction');

        $product->refresh();
        $this->assertEquals('12.000', $product->stock_qty);

        $movement = StockMovement::latest('id')->first();
        $this->assertEquals(-18.0, (float) $movement->quantity);
    }

    public function test_low_stock_triggers_admin_notification_and_supplier_email(): void
    {
        Mail::fake();

        $admin = $this->actingAdmin();
        $supplier = Supplier::create(['name' => 'Kashmir Plants Co', 'email' => 'supplier@example.com', 'is_active' => true]);

        $product = Product::create([
            'name' => 'Apple Plant', 'unit' => 'pcs', 'rate' => 350,
            'stock_qty' => 100, 'low_stock_threshold' => 20,
            'supplier_id' => $supplier->id, 'is_active' => true,
        ]);

        // Drop from 100 to 15 (crosses threshold of 20)
        StockService::record($product, 'out', 85, null, 'Consumed');

        $admin->refresh();
        $this->assertCount(1, $admin->notifications);

        Mail::assertSent(SupplierLowStockMail::class, fn ($mail) => $mail->hasTo('supplier@example.com'));

        // Further drop does not re-trigger (edge-triggered)
        Mail::fake();
        StockService::record($product->refresh(), 'out', 5);

        $this->assertSame(1, $admin->notifications()->count());
        Mail::assertNotSent(SupplierLowStockMail::class);
    }

    public function test_notify_supplier_endpoint_sends_email(): void
    {
        Mail::fake();
        $admin = $this->actingAdmin();
        $supplier = Supplier::create(['name' => 'Net Supplier', 'email' => 'nets@example.com', 'is_active' => true]);

        $product = Product::create([
            'name' => 'Hail Net', 'unit' => 'mtr', 'rate' => 90,
            'stock_qty' => 5, 'low_stock_threshold' => 10,
            'supplier_id' => $supplier->id, 'is_active' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->post("/admin/products/{$product->id}/notify-supplier")
            ->assertRedirect();

        Mail::assertSent(SupplierLowStockMail::class);
    }

    public function test_low_stock_products_can_be_filtered(): void
    {
        $admin = $this->actingAdmin();

        Product::create(['name' => 'Low Item', 'unit' => 'pcs', 'rate' => 10, 'stock_qty' => 2, 'low_stock_threshold' => 5, 'is_active' => true]);
        Product::create(['name' => 'Fine Item', 'unit' => 'pcs', 'rate' => 10, 'stock_qty' => 50, 'low_stock_threshold' => 5, 'is_active' => true]);

        $response = $this->actingAs($admin, 'admin')->get('/admin/products?stock=low');
        $response->assertOk()->assertSee('Low Item')->assertDontSee('Fine Item');
    }
}
