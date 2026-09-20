<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomerLedgerTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private Customer $customer;

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

        $this->customer = Customer::create([
            'name' => 'Tariq Ahmad',
            'phone' => '9906112233',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
    }

    public function test_customer_ledger_calculates_debits_credits_and_running_balance(): void
    {
        // Invoice 1: 5000 (Debit)
        $inv1 = Invoice::create([
            'number' => 'INV-001',
            'customer_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'invoice_date' => now()->subDays(10)->toDateString(),
            'status' => 'partial',
            'grand_total' => 5000.00,
            'amount_paid' => 2000.00,
        ]);

        // Payment 1: 2000 (Credit)
        Payment::create([
            'invoice_id' => $inv1->id,
            'amount' => 2000.00,
            'paid_at' => now()->subDays(8)->toDateString(),
            'method' => 'upi',
            'reference' => 'UPI-TXN-1234',
        ]);

        // Invoice 2: 3000 (Debit)
        Invoice::create([
            'number' => 'INV-002',
            'customer_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'invoice_date' => now()->subDays(3)->toDateString(),
            'status' => 'unpaid',
            'grand_total' => 3000.00,
            'amount_paid' => 0.00,
        ]);

        // Net outstanding = 5000 - 2000 + 3000 = 6000

        // Admin Web view
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.customers.ledger', $this->customer));
        $response->assertOk();
        $response->assertSee('Tariq Ahmad');
        $response->assertSee('INV-001');
        $response->assertSee('INV-002');
        $response->assertSee('UPI-TXN-1234');
        $response->assertSee('6,000.00');

        // Admin CSV Export
        $csv = $this->actingAs($this->admin, 'admin')->get(route('admin.customers.ledger.csv', $this->customer));
        $csv->assertOk();
        $csv->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        // Admin PDF Export
        $pdf = $this->actingAs($this->admin, 'admin')->get(route('admin.customers.ledger.pdf', $this->customer));
        $pdf->assertOk();
        $pdf->assertHeader('Content-Type', 'application/pdf');

        // Customer Mobile API endpoint
        Sanctum::actingAs($this->customer);
        $api = $this->getJson('/api/me/ledger');
        $api->assertOk();
        $api->assertJsonPath('closing_balance', 6000);
        $api->assertJsonPath('total_debit', 8000);
        $api->assertJsonPath('total_credit', 2000);
        $this->assertCount(3, $api->json('transactions'));
    }
}
