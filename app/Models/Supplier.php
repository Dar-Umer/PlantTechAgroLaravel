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
        return (float) $this->purchaseBills()->sum('total_amount');
    }

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function getBalanceDueAttribute(): float
    {
        return (float) $this->purchaseBills()->sum('balance_due');
    }
}
