<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
