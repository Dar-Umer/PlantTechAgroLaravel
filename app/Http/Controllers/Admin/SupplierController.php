<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseBill;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query()
            ->withCount(['products', 'purchaseBills'])
            ->withSum('purchaseBills as total_purchased', 'total_amount')
            ->withSum('purchaseBills as balance_due', 'balance_due')
            ->orderBy('name');

        if ($search = trim((string) $request->query('q'))) {
            $search = addcslashes($search, '%_\\');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('gst_no', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        if ($request->query('status') === 'has_due') {
            $query->having('balance_due', '>', 0);
        }

        $suppliers = $query->paginate(15)->withQueryString();

        $metrics = [
            'total_suppliers' => Supplier::count(),
            'total_bills' => PurchaseBill::where('status', '!=', 'cancelled')->count(),
            'total_purchased' => (float) PurchaseBill::where('status', '!=', 'cancelled')->sum('total_amount'),
            'total_payables' => (float) PurchaseBill::where('status', '!=', 'cancelled')->sum('balance_due'),
        ];

        return view('admin.suppliers.index', compact('suppliers', 'metrics'));
    }

    public function create()
    {
        return view('admin.suppliers.create');
    }

    public function store(Request $request)
    {
        $supplier = Supplier::create($this->validated($request));

        return redirect()->route('admin.suppliers.show', $supplier)->with('success', 'Supplier created successfully.');
    }

    public function show(Supplier $supplier, Request $request)
    {
        $tab = $request->query('tab', 'bills');

        $metrics = [
            'total_purchased' => (float) $supplier->purchaseBills()->where('status', '!=', 'cancelled')->sum('total_amount'),
            'total_paid' => (float) $supplier->payments()->sum('amount'),
            'balance_due' => (float) $supplier->purchaseBills()->where('status', '!=', 'cancelled')->sum('balance_due'),
            'bills_count' => $supplier->purchaseBills()->count(),
            'inward_qty' => (float) $supplier->stockMovements()->where('type', 'in')->sum('quantity'),
        ];

        $bills = null;
        $stockMovements = null;
        $payments = null;
        $products = null;

        if ($tab === 'bills') {
            $bills = $supplier->purchaseBills()
                ->with(['items.product', 'createdBy'])
                ->latest('bill_date')
                ->paginate(15)
                ->withQueryString();
        } elseif ($tab === 'stock') {
            $stockMovements = $supplier->stockMovements()
                ->with(['product', 'batch', 'purchaseBill'])
                ->latest()
                ->paginate(20)
                ->withQueryString();
        } elseif ($tab === 'payments') {
            $payments = $supplier->payments()
                ->with(['purchaseBill', 'createdBy'])
                ->latest('payment_date')
                ->paginate(20)
                ->withQueryString();
        } elseif ($tab === 'products') {
            $products = $supplier->products()
                ->with(['activeBatchesFifo'])
                ->orderBy('name')
                ->get();
        }

        $unpaidBills = $supplier->purchaseBills()
            ->where('balance_due', '>', 0)
            ->where('status', '!=', 'cancelled')
            ->orderBy('bill_date')
            ->get(['id', 'bill_number', 'supplier_invoice_no', 'bill_date', 'total_amount', 'balance_due']);

        return view('admin.suppliers.show', compact('supplier', 'metrics', 'tab', 'bills', 'stockMovements', 'payments', 'products', 'unpaidBills'));
    }

    public function edit(Supplier $supplier)
    {
        return view('admin.suppliers.edit', [
            'supplier' => $supplier,
            'products' => $supplier->products()->orderBy('name')->get(['id', 'name', 'stock_qty', 'unit', 'low_stock_threshold']),
        ]);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $supplier->update($this->validated($request, $supplier->id));

        return redirect()->route('admin.suppliers.show', $supplier)->with('success', 'Supplier updated successfully.');
    }

    public function recordPayment(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'purchase_bill_id' => ['nullable', 'exists:purchase_bills,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'in:cash,bank_transfer,cheque,upi,neft_rtgs,other'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($validated, $supplier) {
            $bill = null;
            if (! empty($validated['purchase_bill_id'])) {
                $bill = $supplier->purchaseBills()->find($validated['purchase_bill_id']);
            }

            $payment = SupplierPayment::create([
                'payment_number' => SupplierPayment::nextPaymentNumber(),
                'supplier_id' => $supplier->id,
                'purchase_bill_id' => $bill?->id,
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'reference_no' => $validated['reference_no'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth('admin')->id(),
            ]);

            if ($bill) {
                $bill->refreshPaymentStatus();
            } else {
                // If payment is unallocated, apply it against oldest unpaid bills automatically
                $remaining = (float) $validated['amount'];
                $dueBills = $supplier->purchaseBills()
                    ->where('balance_due', '>', 0)
                    ->where('status', '!=', 'cancelled')
                    ->orderBy('bill_date')
                    ->get();

                foreach ($dueBills as $dueBill) {
                    if ($remaining <= 0) break;
                    $allocated = min($remaining, (float) $dueBill->balance_due);
                    $remaining -= $allocated;
                    $dueBill->refreshPaymentStatus();
                }
            }
        });

        return back()->with('success', 'Payment of ₹' . number_format($validated['amount'], 2) . ' recorded successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchaseBills()->exists()) {
            return back()->with('error', 'Cannot delete this supplier because purchase bills are attached to it.');
        }

        if ($supplier->products()->exists()) {
            return back()->with('error', 'This supplier has products assigned. Reassign them first.');
        }

        $supplier->delete();

        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier deleted.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'gst_no' => ['nullable', 'string', 'max:64'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
