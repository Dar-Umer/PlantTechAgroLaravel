<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $leadCount = Lead::count();
        $newLeads = Lead::where('status', 'new')->count();

        $customerCount = Customer::count();
        $activeCustomers = Customer::where('status', 'active')->count();

        $workOrderCount = WorkOrder::count();
        $activeWorkOrders = WorkOrder::whereIn('status', ['assigned', 'in_progress'])->count();

        $invoiceCount = Invoice::count();
        $outstanding = (float) Invoice::whereNotIn('status', ['paid', 'cancelled'])
            ->sum(DB::raw('grand_total - amount_paid'));

        $productCount = Product::count();
        $lowStockCount = Product::query()->whereColumn('stock_qty', '<=', 'low_stock_threshold')->count();

        $staffCount = Admin::where('is_active', true)->count();

        $overdueInvoices = Invoice::query()
            ->where('status', 'overdue')
            ->where('due_date', '<', now()->startOfDay())
            ->count();

        $overdueAmount = (float) Invoice::where('status', 'overdue')
            ->sum(DB::raw('grand_total - amount_paid'));

        $staleWorkOrders = config('automation.work_order_reminders_enabled', true)
            ? WorkOrder::query()
                ->whereIn('status', ['assigned', 'in_progress'])
                ->where('updated_at', '<', now()->subDays(max(1, (int) config('automation.work_order_stale_days', 7))))
                ->count()
            : 0;

        $staleLeads = config('automation.lead_escalation_enabled', true)
            ? Lead::query()
                ->whereIn('status', ['new', 'contacted', 'no_answer', 'interested'])
                ->where('updated_at', '<', now()->subDays(max(1, (int) config('automation.lead_stale_days', 3))))
                ->count()
            : 0;

        $collectedThisMonth = (float) Payment::where('paid_at', '>=', now()->startOfMonth())->sum('amount');

        $recentLeads = Lead::query()
            ->latest()
            ->limit(5)
            ->get(['id', 'name', 'phone', 'status', 'created_at']);

        $recentPayments = Payment::query()
            ->with('invoice:id,number')
            ->latest()
            ->limit(5)
            ->get(['id', 'amount', 'method', 'paid_at', 'invoice_id']);

        $recentStockMovements = StockMovement::query()
            ->with('product:id,name,unit')
            ->latest()
            ->limit(6)
            ->get(['id', 'product_id', 'type', 'quantity', 'created_at']);

        return view('admin.dashboard', compact(
            'leadCount', 'newLeads',
            'customerCount', 'activeCustomers',
            'workOrderCount', 'activeWorkOrders',
            'invoiceCount', 'outstanding',
            'productCount', 'lowStockCount',
            'staffCount',
            'overdueInvoices', 'overdueAmount',
            'staleWorkOrders', 'staleLeads',
            'collectedThisMonth',
            'recentLeads', 'recentPayments', 'recentStockMovements',
        ));
    }
}
