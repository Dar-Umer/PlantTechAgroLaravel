<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\WorkOrder;
use App\Services\WeatherService;
use App\Support\AppConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $customer = $request->user();

        $woQuery = WorkOrder::where('customer_id', $customer->id);
        $workOrdersTotal = (clone $woQuery)->count();
        $workOrdersActive = (clone $woQuery)->whereIn('status', ['pending', 'assigned', 'in_progress'])->count();
        $workOrdersInProgress = (clone $woQuery)->whereIn('status', ['assigned', 'in_progress'])->count();
        $workOrdersCompleted = (clone $woQuery)->where('status', 'completed')->count();

        $invoiceQuery = Invoice::where('customer_id', $customer->id);
        $invoicesTotal = (clone $invoiceQuery)->count();
        $outstanding = (float) (clone $invoiceQuery)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->sum(DB::raw('grand_total - amount_paid'));
        $overdueBalance = (float) (clone $invoiceQuery)
            ->where('status', 'overdue')
            ->sum(DB::raw('grand_total - amount_paid'));
        $collectedThisMonth = (float) Payment::where('paid_at', '>=', now()->startOfMonth())
            ->whereHas('invoice', fn ($q) => $q->where('customer_id', $customer->id))
            ->sum('amount');

        $recentWorkOrders = WorkOrder::where('customer_id', $customer->id)
            ->with(['stages:work_order_id,status', 'invoice:id,number,status,grand_total,amount_paid', 'agent:id,name'])
            ->latest()
            ->limit(6)
            ->get();

        $recentInvoices = Invoice::where('customer_id', $customer->id)
            ->with('workOrder:id,number')
            ->latest()
            ->limit(6)
            ->get();

        $orchards = $customer->orchards()->latest()->get();
        $recentOrchards = $orchards->take(4);

        $appConfig = AppConfig::toArray();

        return response()->json([
            'orchardist_id' => $customer->orchardist_id,
            'stats' => [
                'orchards_total' => $orchards->count(),
                'company_orchards_total' => $orchards->where('is_company_established', true)->count(),
                'total_kanals' => round($orchards->sum('area_kanals'), 2),
                'total_plants' => $orchards->sum('tree_count'),
                'work_orders_total' => $workOrdersTotal,
                'work_orders_active' => $workOrdersActive,
                'work_orders_in_progress' => $workOrdersInProgress,
                'work_orders_completed' => $workOrdersCompleted,
                'invoices_total' => $invoicesTotal,
                'outstanding_balance' => round($outstanding, 2),
                'overdue_balance' => round($overdueBalance, 2),
                'collected_this_month' => round($collectedThisMonth, 2),
                'unread_notifications' => $customer->unreadNotifications()->count(),
            ],
            'recent_orchards' => $recentOrchards->map(fn ($orchard) => OrchardController::summary($orchard))->values(),
            'recent_work_orders' => $recentWorkOrders->map(fn (WorkOrder $wo) => WorkOrderController::summary($wo))->values(),
            'recent_invoices' => $recentInvoices->map(fn (Invoice $invoice) => InvoiceController::summary($invoice))->values(),
            'support' => $appConfig['support'],
            'weather' => ((bool) config('weather.enabled', true) && (bool) config('weather.show_api_dashboard', true))
                ? WeatherService::forArea($customer->area)
                : null,
            'app_config' => $appConfig,
        ]);
    }
}