<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GstReportTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

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
    }

    public function test_gst_report_separates_b2b_and_b2c_invoices_and_shows_hsn(): void
    {
        $b2bCustomer = Customer::create([
            'name' => 'Kashmir Agri Traders',
            'phone' => '9906000001',
            'password' => bcrypt('password123'),
            'gstin' => '01AAAAA0000A1Z5',
            'status' => 'active',
        ]);

        $b2cCustomer = Customer::create([
            'name' => 'Ghulam Nabi',
            'phone' => '9906000002',
            'password' => bcrypt('password123'),
            'gstin' => null,
            'status' => 'active',
        ]);

        $product = Product::create([
            'name' => 'Copper Oxychloride 50% WP',
            'sku' => 'COP-50',
            'type' => 'material',
            'unit' => 'kg',
            'stock_quantity' => 100,
            'cost_price' => 300,
            'selling_price' => 500,
            'hsn_code' => '38089290',
            'is_active' => true,
        ]);

        // Create B2B Invoice
        $b2bInv = Invoice::create([
            'number' => 'INV-2026-B2B-1',
            'customer_id' => $b2bCustomer->id,
            'customer_name' => $b2bCustomer->name,
            'invoice_date' => now()->toDateString(),
            'status' => 'paid',
            'subtotal' => 5000,
            'discount_total' => 0,
            'gst_total' => 900,
            'grand_total' => 5900,
        ]);

        $b2bInv->items()->create([
            'product_id' => $product->id,
            'hsn_code' => $product->hsn_code,
            'name' => $product->name,
            'qty' => 10,
            'rate' => 500,
            'gst_rate' => 18,
            'total' => 5000,
            'sort_order' => 0,
        ]);

        // Create B2C Invoice
        $b2cInv = Invoice::create([
            'number' => 'INV-2026-B2C-1',
            'customer_id' => $b2cCustomer->id,
            'customer_name' => $b2cCustomer->name,
            'invoice_date' => now()->toDateString(),
            'status' => 'unpaid',
            'subtotal' => 1000,
            'discount_total' => 0,
            'gst_total' => 180,
            'grand_total' => 1180,
        ]);

        $b2cInv->items()->create([
            'product_id' => $product->id,
            'hsn_code' => $product->hsn_code,
            'name' => $product->name,
            'qty' => 2,
            'rate' => 500,
            'gst_rate' => 18,
            'total' => 1000,
            'sort_order' => 0,
        ]);

        // Fetch GST Report Page
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.reports.gst'));
        $response->assertOk();
        $response->assertSee('Kashmir Agri Traders');
        $response->assertSee('01AAAAA0000A1Z5');
        $response->assertSee('Ghulam Nabi');
        $response->assertSee('38089290');

        // Test B2B CSV Export
        $exportB2b = $this->actingAs($this->admin, 'admin')->get(route('admin.reports.gst.export', ['tab' => 'b2b']));
        $exportB2b->assertOk();
        $exportB2b->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        // Test HSN CSV Export
        $exportHsn = $this->actingAs($this->admin, 'admin')->get(route('admin.reports.gst.export', ['tab' => 'hsn']));
        $exportHsn->assertOk();
        $exportHsn->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
