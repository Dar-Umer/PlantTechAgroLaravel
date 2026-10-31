<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\PosCustomer;
use App\Models\PosSale;
use App\Models\PosSalePayment;
use App\Models\Product;
use App\Models\ProductBatch;
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
            'outstanding_balance' => 0,
        ]);
    }

    public function test_can_complete_pos_checkout_with_stock_deduction_and_zero_taxes(): void
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
        $response->assertJsonPath('grand_total', 650); // 700 - 50 = 650 (NO GST added!)
        $response->assertJsonPath('amount_paid', 650);
        $response->assertJsonPath('balance_due', 0);
        $response->assertJsonPath('payment_status', 'paid');

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
        $this->assertEquals(0.00, (float) $sale->gst_amount); // ZERO TAX
        $this->assertEquals('cash', $sale->payment_method);
        $this->assertEquals(800.00, (float) $sale->amount_tendered);
        $this->assertEquals(650.00, (float) $sale->grand_total);
        $this->assertEquals(650.00, (float) $sale->amount_paid);
        $this->assertEquals(0.00, (float) $sale->balance_due);
        $this->assertEquals('paid', $sale->payment_status);

        // Verify PosSaleItem has zero taxes
        $item = $sale->items->first();
        $this->assertEquals(0.00, (float) $item->tax_rate);
        $this->assertEquals(0.00, (float) $item->tax_amount);
        $this->assertEquals(700.00, (float) $item->total_price);

        // Customer stats updated
        $customer->refresh();
        $this->assertEquals(1, $customer->orders_count);
        $this->assertEquals(650.00, (float) $customer->total_spent);
        $this->assertEquals(0.00, (float) $customer->outstanding_balance);
    }

    public function test_multi_batch_selection_with_different_pricing(): void
    {
        // Add a second batch with a different selling price (e.g. 420.00)
        StockService::record(
            product: $this->product,
            type: 'in',
            quantity: 10,
            reference: 'BATCH-2',
            unitCost: 260.00,
            batchNumber: 'LOT-PREMIUM-2026'
        );

        $premiumBatch = ProductBatch::where('batch_number', 'LOT-PREMIUM-2026')->first();
        $premiumBatch->update(['selling_price' => 420.00]);

        $this->product->refresh();
        $this->assertEquals(30.0, (float) $this->product->stock_qty);

        // Sell from the premium batch specifically
        $response = $this->actingAs($this->admin, 'admin')->postJson(route('admin.pos.checkout'), [
            'customer_name' => 'Walk-in Customer',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'batch_id' => $premiumBatch->id,
                    'quantity' => 3,
                    'unit_price' => 420.00,
                ],
            ],
            'payment_method' => 'upi',
        ]);

        $response->assertOk();
        $response->assertJsonPath('grand_total', 1260); // 3 * 420 = 1260

        // Stock in the premium batch specifically should drop from 10 to 7
        $premiumBatch->refresh();
        $this->assertEquals(7.0, (float) $premiumBatch->current_qty);

        $sale = PosSale::latest()->first();
        $saleItem = $sale->items->first();
        $this->assertEquals($premiumBatch->id, $saleItem->batch_id);
        $this->assertEquals(420.00, (float) $saleItem->unit_price);
        $this->assertEquals(260.00, (float) $saleItem->cost_price);
    }

    public function test_split_payments_across_multiple_methods(): void
    {
        $customer = PosCustomer::create([
            'name' => 'Bashir Ahmad',
            'phone' => '9419000111',
        ]);

        // Bill: 2 * 350 = 700. Pay: 350 Cash + 350 UPI
        $response = $this->actingAs($this->admin, 'admin')->postJson(route('admin.pos.checkout'), [
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'unit_price' => 350.00,
                ],
            ],
            'payment_method' => 'split',
            'payments' => [
                ['method' => 'cash', 'amount' => 350.00],
                ['method' => 'upi', 'amount' => 350.00],
            ],
        ]);

        $response->assertOk();
        $response->assertJsonPath('grand_total', 700);
        $response->assertJsonPath('amount_paid', 700);
        $response->assertJsonPath('balance_due', 0);
        $response->assertJsonPath('payment_status', 'paid');

        $sale = PosSale::latest()->first();
        $this->assertEquals('split', $sale->payment_method);
        $this->assertEquals(700.00, (float) $sale->amount_paid);
        $this->assertEquals(0.00, (float) $sale->balance_due);
        $this->assertCount(2, $sale->payments);

        $this->assertDatabaseHas('pos_sale_payments', [
            'pos_sale_id' => $sale->id,
            'method' => 'cash',
            'amount' => 350.00,
        ]);
        $this->assertDatabaseHas('pos_sale_payments', [
            'pos_sale_id' => $sale->id,
            'method' => 'upi',
            'amount' => 350.00,
        ]);
    }

    public function test_partial_payment_tracks_customer_outstanding_balance(): void
    {
        $customer = PosCustomer::create([
            'name' => 'Farooq Lone',
            'phone' => '9419555666',
            'outstanding_balance' => 0,
        ]);

        // Bill: 1000 (selling 2 items @ 500)
        // Customer pays 600 (400 Cash + 200 UPI), 400 remains as balance due
        $response = $this->actingAs($this->admin, 'admin')->postJson(route('admin.pos.checkout'), [
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'unit_price' => 500.00,
                ],
            ],
            'payment_method' => 'split',
            'payments' => [
                ['method' => 'cash', 'amount' => 400.00],
                ['method' => 'upi', 'amount' => 200.00],
            ],
        ]);

        $response->assertOk();
        $response->assertJsonPath('grand_total', 1000);
        $response->assertJsonPath('amount_paid', 600);
        $response->assertJsonPath('balance_due', 400);
        $response->assertJsonPath('payment_status', 'partial');

        // Customer's outstanding balance increased by 400
        $customer->refresh();
        $this->assertEquals(400.00, (float) $customer->outstanding_balance);
        $this->assertEquals(600.00, (float) $customer->total_spent);
    }

    public function test_settle_customer_balance(): void
    {
        $customer = PosCustomer::create([
            'name' => 'Ghulam Nabi',
            'phone' => '9419888999',
            'outstanding_balance' => 500.00,
            'total_spent' => 1000.00,
        ]);

        $sale = PosSale::create([
            'invoice_number' => 'POS/2026-27/0077',
            'pos_customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'sale_date' => now(),
            'status' => PosSale::STATUS_COMPLETED,
            'subtotal' => 500.00,
            'grand_total' => 500.00,
            'amount_paid' => 0.00,
            'balance_due' => 500.00,
            'payment_status' => PosSale::PAYMENT_STATUS_UNPAID,
            'payment_method' => 'cash',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin, 'admin')->postJson(route('admin.pos.customers.settle-balance', $customer), [
            'amount' => 300.00,
            'payment_method' => 'cash',
            'note' => 'Paid 300 cash for previous bill balance',
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('new_balance', 200);

        $customer->refresh();
        $this->assertEquals(200.00, (float) $customer->outstanding_balance);
        $this->assertEquals(1300.00, (float) $customer->total_spent);

        // Verify the underlying POS Sale was updated and balance due decreased
        $sale->refresh();
        $this->assertEquals(300.00, (float) $sale->amount_paid);
        $this->assertEquals(200.00, (float) $sale->balance_due);
        $this->assertEquals(PosSale::PAYMENT_STATUS_PARTIAL, $sale->payment_status);

        // Check Sales Report reflects the reduced balance due
        $reportResp = $this->actingAs($this->admin, 'admin')->get(route('admin.reports.sales', [
            'from' => now()->subDay()->toDateString(),
            'to' => now()->addDay()->toDateString(),
        ]));
        $reportResp->assertOk();
        $reportResp->assertSee('200.00');
    }

    public function test_record_payment_directly_on_sale_reduces_balance(): void
    {
        $customer = PosCustomer::create([
            'name' => 'Nazir Ahmad',
            'phone' => '9419111222',
            'outstanding_balance' => 400.00,
        ]);

        $sale = PosSale::create([
            'invoice_number' => 'POS/2026-27/0088',
            'pos_customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'sale_date' => now(),
            'status' => PosSale::STATUS_COMPLETED,
            'subtotal' => 400.00,
            'grand_total' => 400.00,
            'amount_paid' => 0.00,
            'balance_due' => 400.00,
            'payment_status' => PosSale::PAYMENT_STATUS_UNPAID,
            'payment_method' => 'cash',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.pos.sales.payments.store', $sale), [
            'amount' => 400.00,
            'payment_method' => 'upi',
            'reference' => 'UPI-TXN-123456',
        ]);

        $response->assertRedirect();

        $sale->refresh();
        $this->assertEquals(400.00, (float) $sale->amount_paid);
        $this->assertEquals(0.00, (float) $sale->balance_due);
        $this->assertEquals(PosSale::PAYMENT_STATUS_PAID, $sale->payment_status);

        $customer->refresh();
        $this->assertEquals(0.00, (float) $customer->outstanding_balance);
    }

    public function test_can_view_receipt_and_invoice(): void
    {
        $sale = PosSale::create([
            'invoice_number' => 'POS/2026-27/0001',
            'customer_name' => 'Walk-in Customer',
            'sale_date' => now(),
            'status' => PosSale::STATUS_COMPLETED,
            'subtotal' => 350.00,
            'grand_total' => 350.00,
            'amount_paid' => 350.00,
            'balance_due' => 0.00,
            'payment_status' => 'paid',
            'payment_method' => 'upi',
            'created_by' => $this->admin->id,
        ]);

        $supplier = \App\Models\Supplier::create(['name' => 'Secret Vendor Ltd', 'phone' => '1234567890']);

        $sale->items()->create([
            'product_id' => $this->product->id,
            'supplier_id' => $supplier->id,
            'product_name' => $this->product->name,
            'unit' => 'bottle',
            'unit_price' => 350.00,
            'cost_price' => 200.00,
            'quantity' => 1,
            'tax_rate' => 0.00,
            'tax_amount' => 0.00,
            'total_price' => 350.00,
        ]);

        // 80mm Receipt (No tax lines, no vendor/supplier exposure)
        $receiptResp = $this->actingAs($this->admin, 'admin')->get(route('admin.pos.receipt', $sale));
        $receiptResp->assertOk();
        $receiptResp->assertSee('RETAIL RECEIPT / CASH BILL');
        $receiptResp->assertDontSee('Total GST');
        $receiptResp->assertDontSee('Secret Vendor Ltd');
        $receiptResp->assertSee('POS/2026-27/0001');

        // A4 Retail Invoice (No tax lines, no vendor/supplier exposure)
        $invoiceResp = $this->actingAs($this->admin, 'admin')->get(route('admin.pos.invoice', $sale));
        $invoiceResp->assertOk();
        $invoiceResp->assertSee('PLANT TECH AGRO');
        $invoiceResp->assertSee('Retail Invoice');
        $invoiceResp->assertDontSee('Total GST Amount');
        $invoiceResp->assertDontSee('Secret Vendor Ltd');
        $invoiceResp->assertSee('POS/2026-27/0001');
    }

    public function test_can_cancel_pos_sale_and_reverse_inventory(): void
    {
        $customer = PosCustomer::create([
            'name' => 'Zahoor Lone',
            'outstanding_balance' => 0,
        ]);

        $this->actingAs($this->admin, 'admin')->postJson(route('admin.pos.checkout'), [
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 5,
                    'unit_price' => 350.00,
                ],
            ],
            'payment_method' => 'cash',
            'amount_tendered' => 1000.00, // Total 1750, paid 1000, 750 due
        ]);

        $this->product->refresh();
        $this->assertEquals(15.0, (float) $this->product->stock_qty);

        $customer->refresh();
        $this->assertEquals(750.00, (float) $customer->outstanding_balance);

        $sale = PosSale::latest()->first();

        // Cancel sale
        $cancelResp = $this->actingAs($this->admin, 'admin')->post(route('admin.pos.sales.cancel', $sale));
        $cancelResp->assertRedirect();

        $sale->refresh();
        $this->assertEquals(PosSale::STATUS_CANCELLED, $sale->status);

        // Stock restored back from 15 to 20
        $this->product->refresh();
        $this->assertEquals(20.0, (float) $this->product->stock_qty);

        // Customer balance reversed back to 0
        $customer->refresh();
        $this->assertEquals(0.00, (float) $customer->outstanding_balance);
    }

    public function test_pos_checkout_with_labour_and_installation_charges_and_profit_calculation(): void
    {
        $customer = PosCustomer::create([
            'name' => 'Fayaz Rather',
            'phone' => '9419001122',
            'outstanding_balance' => 0,
        ]);

        // Product rate is 200 (cost), selling_price is 350
        // Buy 2 units = 700. Internal cost = 400. Item profit = 300.
        // Labour customer rate = 800, Labour actual cost = 450. Labour profit = 350.
        // Total expected profit = 300 + 350 = 650.
        // Net Grand total = 700 + 800 = 1500.
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
            'labour_charges' => 800.00,
            'labour_cost' => 450.00,
            'discount_amount' => 0,
            'payment_method' => 'cash',
            'amount_tendered' => 1500.00,
            'amount_paid' => 1500.00,
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('grand_total', 1500);

        $sale = PosSale::latest()->first();
        $this->assertNotNull($sale);
        $this->assertEquals(700.00, (float) $sale->subtotal);
        $this->assertEquals(800.00, (float) $sale->labour_charges);
        $this->assertEquals(450.00, (float) $sale->labour_cost);
        $this->assertEquals(1500.00, (float) $sale->grand_total);
        $this->assertEquals(350.00, $sale->labourProfit());
        $this->assertEquals(650.00, $sale->totalProfit());

        // Check receipt: Customer rate (800) must appear, internal cost (450) must NOT appear
        $receiptResp = $this->actingAs($this->admin, 'admin')->get(route('admin.pos.receipt', $sale));
        $receiptResp->assertOk();
        $receiptResp->assertSee('Labour / Installation');
        $receiptResp->assertSee('800.00');
        $receiptResp->assertDontSee('450.00');

        // Check invoice: Customer rate (800) must appear, internal cost (450) must NOT appear
        $invoiceResp = $this->actingAs($this->admin, 'admin')->get(route('admin.pos.invoice', $sale));
        $invoiceResp->assertOk();
        $invoiceResp->assertSee('Labour / Installation Charges');
        $invoiceResp->assertSee('800.00');
        $invoiceResp->assertDontSee('450.00');

        // Check admin show page: Shows Labour charges, Internal cost, and Profit
        $showResp = $this->actingAs($this->admin, 'admin')->get(route('admin.pos.sales.show', $sale));
        $showResp->assertOk();
        $showResp->assertSee('800.00');
        $showResp->assertSee('Internal Labour Cost');
        $showResp->assertSee('450.00');
        $showResp->assertSee('Labour Profit');
        $showResp->assertSee('Estimated Net Profit');
    }

    public function test_sales_page_has_vertical_scrolling_and_terminal_remains_docked(): void
    {
        // 1. Sales list page (/sales and /admin/pos/sales) must have overflow-y-auto so mouse wheel scrolling works
        $salesResp = $this->actingAs($this->admin, 'admin')->get(route('pos.sales'));
        $salesResp->assertOk();
        $salesResp->assertSee('overflow-y-auto', false);
        $salesResp->assertDontSee('<main class="flex-1 flex flex-col overflow-hidden', false);

        $adminSalesResp = $this->actingAs($this->admin, 'admin')->get(route('admin.pos.sales'));
        $adminSalesResp->assertOk();
        $adminSalesResp->assertSee('overflow-y-auto', false);

        // 2. Terminal page (/terminal and /admin/pos) must remain docked full height without outer scrollbars
        $terminalResp = $this->actingAs($this->admin, 'admin')->get(route('pos.terminal'));
        $terminalResp->assertOk();
        $terminalResp->assertSee('overflow-hidden', false);
    }

    public function test_terminal_cart_inputs_support_auto_tab_from_qty_to_rate(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('pos.terminal'));
        $response->assertOk();
        $response->assertSee('handleQtyBlur', false);
        $response->assertSee('focusRate', false);
        $response->assertSee(':id="\'cart-rate-\' + idx"', false);
        $response->assertSee(':id="\'cart-qty-\' + idx"', false);
    }

    public function test_sales_list_and_invoice_details_page_proper_records_and_due_collection(): void
    {
        $customer = PosCustomer::create([
            'name' => 'Ghulam Rasool',
            'phone' => '9419123456',
        ]);

        $checkoutResp = $this->actingAs($this->admin, 'admin')->postJson(route('admin.pos.checkout'), [
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'unit_price' => 500.00,
                ],
            ],
            'payment_method' => 'cash',
            'amount_tendered' => 600.00,
        ]);
        $checkoutResp->assertOk();
        $sale = PosSale::latest()->first();
        $this->assertEquals(400.00, (float) $sale->balance_due);

        // 1. Check Sales List Page (/sales)
        $salesListResp = $this->actingAs($this->admin, 'admin')->get(route('pos.sales'));
        $salesListResp->assertOk();
        $salesListResp->assertSee($sale->invoice_number);
        $salesListResp->assertSee(route('pos.sales.show', $sale));
        $salesListResp->assertSee('Receive Due');
        $salesListResp->assertSee('receiveModal');

        // 2. Check Invoice Details Page (/sales/{id})
        $showResp = $this->actingAs($this->admin, 'admin')->get(route('pos.sales.show', $sale));
        $showResp->assertOk();
        $showResp->assertSee('Products & Items Sold', false);
        $showResp->assertSee($this->product->name);
        $showResp->assertSee($this->product->sku);
        $showResp->assertSee('500.00');
        $showResp->assertSee('1,000.00');

        $showResp->assertSee('Payment Track Record', false);
        $showResp->assertSee('600.00');
        $showResp->assertSee('Received & Verified', false);
        $showResp->assertSee('Remaining Due to Collect', false);
        $showResp->assertSee('400.00');

        $showResp->assertSee('Receive Outstanding Payment', false);
        $showResp->assertSee(route('pos.sales.payments.store', $sale));

        // 3. Customer name should link to customer profile
        $this->assertNotNull($customer);
        $showResp->assertSee(route('pos.customers.show', $customer));
        $salesListResp->assertSee(route('pos.customers.show', $customer));
    }

    public function test_customer_profile_and_statement_flow(): void
    {
        $customer = PosCustomer::create([
            'name' => 'John Doe Orchardist',
            'phone' => '9988776655',
            'email' => 'john.doe@orchard.com',
            'address' => 'Apple Orchard Lane, Shop 4',
            'gstin' => '01ABCDE1234F1Z5',
            'outstanding_balance' => 0,
        ]);

        // Create Sale with Partial Payment
        $checkoutResp = $this->actingAs($this->admin, 'admin')->postJson(route('pos.checkout'), [
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'customer_gstin' => $customer->gstin,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 4,
                    'unit_price' => 500.00,
                ],
            ],
            'payment_method' => 'cash',
            'amount_tendered' => 1200.00, // Total is 2000, 800 balance due
        ]);
        $checkoutResp->assertOk();

        $customer->refresh();
        $this->assertEquals(800.00, (float) $customer->outstanding_balance);
        $this->assertEquals(1200.00, (float) $customer->total_spent);
        $this->assertEquals(1, $customer->orders_count);

        // 1. Visit Customer Profile (/customers/{customer})
        $profileResp = $this->actingAs($this->admin, 'admin')->get(route('pos.customers.show', $customer));
        $profileResp->assertOk();
        $profileResp->assertSee('John Doe Orchardist');
        $profileResp->assertSee('9988776655');
        $profileResp->assertSee('01ABCDE1234F1Z5');
        $profileResp->assertSee('B2B / GST', false);
        $profileResp->assertSee('800.00'); // Due balance
        $profileResp->assertSee('2,000.00'); // Lifetime billed
        $profileResp->assertSee('1,200.00'); // Total paid
        $profileResp->assertSee('Account Statement & Ledger', false);
        $profileResp->assertSee('Retail Invoice');
        $profileResp->assertSee('Payment via Cash');
        $profileResp->assertSee(route('pos.customers.statement', $customer));

        // 2. Visit Customer Printable Statement (/customers/{customer}/statement)
        $statementResp = $this->actingAs($this->admin, 'admin')->get(route('pos.customers.statement', $customer));
        $statementResp->assertOk();
        $statementResp->assertSee('Account Statement');
        $statementResp->assertSee('John Doe Orchardist');
        $statementResp->assertSee('800.00');
        $statementResp->assertSee('2,000.00');

        // 3. Settle balance via standard form POST (redirect response)
        $settleResp = $this->actingAs($this->admin, 'admin')->post(route('pos.customers.settle-balance', $customer), [
            'amount' => 500.00,
            'payment_method' => 'upi',
            'note' => 'GPay settlement',
        ]);
        $settleResp->assertRedirect();
        $settleResp->assertSessionHas('success');

        $customer->refresh();
        $this->assertEquals(300.00, (float) $customer->outstanding_balance);
        $this->assertEquals(1700.00, (float) $customer->total_spent);

        // Verify updated statement shows new payment and closing balance 300.00
        $updatedProfileResp = $this->actingAs($this->admin, 'admin')->get(route('pos.customers.show', $customer));
        $updatedProfileResp->assertOk();
        $updatedProfileResp->assertSee('300.00');
        $updatedProfileResp->assertSee('Payment via UPI / QR');
    }

    public function test_pos_customers_directory_index_and_filters(): void
    {
        $c1 = PosCustomer::create([
            'name' => 'Ghulam Hassan',
            'phone' => '9419111222',
            'outstanding_balance' => 450.00,
            'total_spent' => 2000.00,
            'orders_count' => 3,
        ]);

        $c2 = PosCustomer::create([
            'name' => 'Tariq Ahmad',
            'phone' => '9419333444',
            'outstanding_balance' => 0.00,
            'total_spent' => 1500.00,
            'orders_count' => 2,
        ]);

        $c3 = PosCustomer::create([
            'name' => 'Kashmir Agro Ltd',
            'phone' => '9419555666',
            'gstin' => '01AAAAA0000A1Z5',
            'outstanding_balance' => 1200.00,
            'total_spent' => 10000.00,
            'orders_count' => 5,
        ]);

        // 1. Visit /customers
        $resp = $this->actingAs($this->admin, 'admin')->get('/customers');
        $resp->assertOk();
        $resp->assertSee('POS Retail Customers');
        $resp->assertSee('Ghulam Hassan');
        $resp->assertSee('Tariq Ahmad');
        $resp->assertSee('Kashmir Agro Ltd');
        $resp->assertSee('450.00');
        $resp->assertSee('1,200.00');
        $resp->assertSee(route('pos.customers.show', $c1));

        // 2. Filter dues only
        $duesResp = $this->actingAs($this->admin, 'admin')->get(route('pos.customers.index', ['status' => 'dues']));
        $duesResp->assertOk();
        $duesResp->assertSee('Ghulam Hassan');
        $duesResp->assertSee('Kashmir Agro Ltd');
        $duesResp->assertDontSee('Tariq Ahmad');

        // 3. Search query
        $searchResp = $this->actingAs($this->admin, 'admin')->get(route('pos.customers.index', ['q' => 'Ghulam']));
        $searchResp->assertOk();
        $searchResp->assertSee('Ghulam Hassan');
        $searchResp->assertDontSee('Kashmir Agro Ltd');

        // 4. Admin route access
        $adminResp = $this->actingAs($this->admin, 'admin')->get('/admin/pos/customers');
        $adminResp->assertOk();
        $adminResp->assertSee('POS Retail Customers');

        // 5. Store customer with standard form redirect
        $storeResp = $this->actingAs($this->admin, 'admin')->post(route('pos.customers.store'), [
            'name' => 'New Retail Buyer',
            'phone' => '9419888999',
            'email' => 'buyer@example.com',
            'address' => 'Baramulla',
        ]);
        $storeResp->assertRedirect();
        $this->assertDatabaseHas('pos_customers', [
            'name' => 'New Retail Buyer',
            'phone' => '9419888999',
        ]);
    }
}
