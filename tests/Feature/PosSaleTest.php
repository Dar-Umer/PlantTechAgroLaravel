<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\PosCustomer;
use App\Models\PosSale;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PosSaleTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Super Admin', 'guard_name' => 'admin']);
        $this->admin = Admin::create([
            'name' => 'Cashier Operator',
            'email' => 'cashier@test.com',
            'password' => bcrypt('password'),
            'phone' => '9888888888',
            'role' => 'admin',
            'is_active' => true,
        ]);
        $this->admin->assignRole('Super Admin');

        $this->product = Product::create([
            'name' => 'Organic Bio-Stimulant 1L',
            'sku' => 'BIO-STIM-01',
            'type' => 'sellable',
            'unit' => 'bottle',
            'stock_qty' => 0,
            'rate' => 200.00,
            'selling_price' => 350.00,
            'gst_rate' => 18.00,
            'is_active' => true,
        ]);

        // Stock in 20 units @ cost 200
        StockService::record(
            product: $this->product,
            type: 'in',
            quantity: 20,
            reference: 'OPENING',
            unitCost: 200.00,
            batchNumber: 'LOT-POS-TEST'
        );
    }

    public function test_can_view_pos_terminal(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.pos.terminal'));
        $response->assertOk();
        $response->assertSee('POS Terminal');
        $response->assertSee('Organic Bio-Stimulant 1L');
    }

    public function test_can_add_pos_customer_via_ajax(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->postJson(route('admin.pos.customers.store'), [
            'name' => 'Mohammad Tariq',
            'phone' => '9419123456',
            'gstin' => '01AAAAA1234A1Z1',
            'address' => 'Shop 4, Fruit Mandi, Sopore',
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('customer.name', 'Mohammad Tariq');

        $this->assertDatabaseHas('pos_customers', [
            'name' => 'Mohammad Tariq',
            'phone' => '9419123456',
        ]);
    }

    public function test_can_complete_pos_checkout_with_stock_deduction(): void
    {
        $customer = PosCustomer::create([
            'name' => 'Gulzar Ahmad',
            'phone' => '9797000000',
        ]);

        $response = $this->actingAs($this->admin, 'admin')->postJson(route('admin.pos.checkout'), [
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'unit_price' => 350.00,
                ],
            ],
            'discount_amount' => 50.00,
            'payment_method' => 'cash',
            'amount_tendered' => 800.00,
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);

        // Product stock should have dropped from 20 to 18
        $this->product->refresh();
        $this->assertEquals(18.0, (float) $this->product->stock_qty);

        // Verify PosSale record
        $sale = PosSale::latest()->first();
        $this->assertNotNull($sale);
        $this->assertEquals($customer->id, $sale->pos_customer_id);
        $this->assertStringStartsWith('POS/', $sale->invoice_number);
        $this->assertEquals(PosSale::STATUS_COMPLETED, $sale->status);
        $this->assertEquals(50.00, (float) $sale->discount_amount);
        $this->assertEquals('cash', $sale->payment_method);
        $this->assertEquals(800.00, (float) $sale->amount_tendered);

        // Customer stats updated
        $customer->refresh();
        $this->assertEquals(1, $customer->orders_count);
        $this->assertEquals($sale->grand_total, (float) $customer->total_spent);
    }

    public function test_can_view_receipt_and_invoice(): void
    {
        $sale = PosSale::create([
            'invoice_number' => 'POS/2026-27/0001',
            'customer_name' => 'Walk-in Customer',
            'sale_date' => now(),
            'status' => PosSale::STATUS_COMPLETED,
            'subtotal' => 350.00,
            'grand_total' => 413.00,
            'payment_method' => 'upi',
            'created_by' => $this->admin->id,
        ]);

        $sale->items()->create([
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'unit' => 'bottle',
            'unit_price' => 350.00,
            'cost_price' => 200.00,
            'quantity' => 1,
            'tax_rate' => 18.00,
            'tax_amount' => 63.00,
            'total_price' => 413.00,
        ]);

        // 80mm Receipt
        $receiptResp = $this->actingAs($this->admin, 'admin')->get(route('admin.pos.receipt', $sale));
        $receiptResp->assertOk();
        $receiptResp->assertSee('TAX INVOICE / RETAIL RECEIPT');
        $receiptResp->assertSee('POS/2026-27/0001');

        // A4 Tax Invoice
        $invoiceResp = $this->actingAs($this->admin, 'admin')->get(route('admin.pos.invoice', $sale));
        $invoiceResp->assertOk();
        $invoiceResp->assertSee('PLANT TECH AGRO');
        $invoiceResp->assertSee('POS/2026-27/0001');
    }

    public function test_can_cancel_pos_sale_and_reverse_inventory(): void
    {
        $this->actingAs($this->admin, 'admin')->postJson(route('admin.pos.checkout'), [
            'customer_name' => 'Walk-in Customer',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 5,
                    'unit_price' => 350.00,
                ],
            ],
            'payment_method' => 'card',
        ]);

        $this->product->refresh();
        $this->assertEquals(15.0, (float) $this->product->stock_qty);

        $sale = PosSale::latest()->first();

        // Cancel sale
        $cancelResp = $this->actingAs($this->admin, 'admin')->post(route('admin.pos.sales.cancel', $sale));
        $cancelResp->assertRedirect();

        $sale->refresh();
        $this->assertEquals(PosSale::STATUS_CANCELLED, $sale->status);

        // Stock restored back from 15 to 20
        $this->product->refresh();
        $this->assertEquals(20.0, (float) $this->product->stock_qty);
    }
}
