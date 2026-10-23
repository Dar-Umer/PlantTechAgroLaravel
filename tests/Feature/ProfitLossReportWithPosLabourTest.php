<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\PosCustomer;
use App\Models\PosSale;
use App\Models\PosSaleItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfitLossReportWithPosLabourTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Super Admin', 'guard_name' => 'admin']);
        $this->admin = Admin::create([
            'name' => 'Accountant Admin',
            'email' => 'accounts@test.com',
            'password' => bcrypt('password'),
            'phone' => '9888888889',
            'role' => 'admin',
            'is_active' => true,
        ]);
        $this->admin->assignRole('Super Admin');
    }

    public function test_profit_and_loss_report_includes_pos_labour_charges_in_revenue_and_cost_in_cogs(): void
    {
        $today = Carbon::today();

        $product = Product::create([
            'name' => 'Irrigation Pipe 50mm',
            'sku' => 'PIPE-50',
            'type' => 'sellable',
            'unit' => 'mtr',
            'stock_qty' => 100,
            'rate' => 50.00,
            'selling_price' => 80.00,
            'is_active' => true,
        ]);

        $sale = PosSale::create([
            'invoice_number' => 'POS/2026-27/9999',
            'customer_name' => 'Bilal Dar',
            'sale_date' => $today->copy()->setHour(11),
            'status' => PosSale::STATUS_COMPLETED,
            'subtotal' => 800.00, // 10 units @ 80
            'labour_charges' => 600.00, // Customer charged 600
            'labour_cost' => 250.00, // Cost to us 250 (Profit = +350)
            'discount_amount' => 0.00,
            'discount_type' => 'fixed',
            'gst_amount' => 0.00,
            'round_off' => 0.00,
            'grand_total' => 1400.00, // 800 + 600
            'amount_paid' => 1400.00,
            'balance_due' => 0.00,
            'payment_status' => PosSale::PAYMENT_STATUS_PAID,
            'payment_method' => 'cash',
            'amount_tendered' => 1400.00,
            'change_amount' => 0.00,
            'created_by' => $this->admin->id,
        ]);

        PosSaleItem::create([
            'pos_sale_id' => $sale->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit' => 'mtr',
            'unit_price' => 80.00,
            'cost_price' => 50.00,
            'quantity' => 10,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_price' => 800.00,
        ]);

        // 1. Check P&L Report
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.reports.profit-loss', [
            'from' => $today->toDateString(),
            'to' => $today->toDateString(),
        ]));

        $response->assertOk();
        // Revenue should include 800 + 600 = 1400
        $response->assertSee('1,400.00');
        // COGS should include 250 labour cost
        $response->assertSee('250.00');
        $response->assertSee('POS Labour &amp; Installation Direct Cost', false);

        // 2. Check Bill-wise Profit Report
        $profitAnalysisResp = $this->actingAs($this->admin, 'admin')->get(route('admin.reports.bill-wise-profit', [
            'from' => $today->toDateString(),
            'to' => $today->toDateString(),
        ]));

        $profitAnalysisResp->assertOk();
        $profitAnalysisResp->assertSee('POS/2026-27/9999');
        // Sale Value = 800 + 600 = 1400
        $profitAnalysisResp->assertSee('1,400.00');
        // Estimated Cost = (10 * 50 = 500) + 250 = 750
        $profitAnalysisResp->assertSee('750.00');
        // Gross Profit = 1400 - 750 = 650
        $profitAnalysisResp->assertSee('650.00');
    }
}
