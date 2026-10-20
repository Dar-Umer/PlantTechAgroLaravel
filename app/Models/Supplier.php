<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Supplier extends Model
{
    protected $fillable = [
        'name', 'contact_person', 'phone', 'email', 'address', 'gst_no', 'notes', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function purchaseBills()
    {
        return $this->hasMany(PurchaseBill::class);
    }

    public function purchase_bills()
    {
        return $this->purchaseBills();
    }

    public function payments()
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function batches()
    {
        return $this->hasMany(ProductBatch::class);
    }

    /**
     * Query all products supplied by this supplier (as primary supplier, via purchase bills, batches, or movements).
     */
    public function suppliedProductsQuery()
    {
        return Product::where(function ($q) {
            $q->where('supplier_id', $this->id)
              ->orWhereHas('suppliers', fn($s) => $s->where('suppliers.id', $this->id))
              ->orWhereHas('batches', fn($b) => $b->where('supplier_id', $this->id))
              ->orWhereHas('movements', fn($m) => $m->where('supplier_id', $this->id))
              ->orWhereExists(function ($sub) {
                  $sub->select(DB::raw(1))
                      ->from('purchase_bill_items')
                      ->join('purchase_bills', 'purchase_bills.id', '=', 'purchase_bill_items.purchase_bill_id')
                      ->whereColumn('purchase_bill_items.product_id', 'products.id')
                      ->where('purchase_bills.supplier_id', $this->id);
              });
        });
    }

    /**
     * Get all supplied products with supplier-specific stock and active batches populated.
     */
    public function getSuppliedProducts()
    {
        $products = $this->suppliedProductsQuery()
            ->with([
                'batches' => fn($b) => $b->where('supplier_id', $this->id)
                                         ->where('current_qty', '>', 0)
                                         ->orderByRaw('COALESCE(inward_date, created_at) ASC, id ASC'),
            ])
            ->orderBy('name')
            ->get();

        foreach ($products as $prod) {
            $prod->supplier_stock = $prod->getSupplierStock($this->id);
        }

        return $products;
    }

    public function getTotalPurchasedAttribute(): float
    {
        return (float) $this->purchaseBills()->where('status', '!=', 'cancelled')->sum('total_amount');
    }

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function getBalanceDueAttribute(): float
    {
        return max(0, round($this->total_purchased - $this->total_paid, 2));
    }

    /**
     * Reconcile any unallocated payments against unpaid purchase bills in FIFO order.
     */
    public function reconcilePayments(): void
    {
        $unallocatedPayments = $this->payments()
            ->whereNull('purchase_bill_id')
            ->orderBy('payment_date')
            ->orderBy('id')
            ->get();

        if ($unallocatedPayments->isEmpty()) {
            return;
        }

        $unpaidBills = $this->purchaseBills()
            ->where('status', '!=', 'cancelled')
            ->where('balance_due', '>', 0)
            ->orderBy('bill_date')
            ->orderBy('id')
            ->get();

        if ($unpaidBills->isEmpty()) {
            return;
        }

        foreach ($unallocatedPayments as $payment) {
            $paymentRemaining = (float) $payment->amount;

            foreach ($unpaidBills as $bill) {
                if ($paymentRemaining <= 0) {
                    break;
                }

                $billDue = (float) $bill->balance_due;
                if ($billDue <= 0) {
                    continue;
                }

                if ($paymentRemaining <= $billDue) {
                    // This payment fits entirely into this bill
                    $payment->update(['purchase_bill_id' => $bill->id]);
                    $bill->refreshPaymentStatus();
                    $paymentRemaining = 0;
                    break;
                } else {
                    // Payment is larger than this bill's due amount.
                    // Allocate $billDue to this bill, and create remaining payment record.
                    $payment->update([
                        'purchase_bill_id' => $bill->id,
                        'amount' => $billDue,
                    ]);
                    $bill->refreshPaymentStatus();

                    $paymentRemaining = round($paymentRemaining - $billDue, 2);

                    $payment = SupplierPayment::create([
                        'payment_number' => SupplierPayment::nextPaymentNumber(),
                        'supplier_id' => $this->id,
                        'purchase_bill_id' => null,
                        'amount' => $paymentRemaining,
                        'payment_date' => $payment->payment_date,
                        'payment_method' => $payment->payment_method,
                        'reference_no' => $payment->reference_no,
                        'notes' => $payment->notes,
                        'created_by' => $payment->created_by,
                    ]);
                }
            }
        }
    }
}
