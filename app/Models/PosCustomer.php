<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosCustomer extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'name',
        'phone',
        'email',
        'address',
        'gstin',
        'notes',
        'total_spent',
        'outstanding_balance',
        'orders_count',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'total_spent' => 'decimal:2',
            'outstanding_balance' => 'decimal:2',
            'orders_count' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function posSales(): HasMany
    {
        return $this->hasMany(PosSale::class)->latest('sale_date');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function isB2B(): bool
    {
        return ! empty($this->gstin);
    }

    public static function walkIn(): self
    {
        return static::firstOrCreate(
            ['name' => 'Walk-in Customer', 'phone' => null],
            ['notes' => 'Default POS retail walk-in customer']
        );
    }
}
