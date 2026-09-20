<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductBatch extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_NEAR_EXPIRY = 'near_expiry';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_DEPLETED = 'depleted';

    public const STATUSES = [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_NEAR_EXPIRY => 'Near Expiry',
        self::STATUS_EXPIRED => 'Expired',
        self::STATUS_DEPLETED => 'Depleted',
    ];

    public const STATUS_COLORS = [
        self::STATUS_ACTIVE => 'green',
        self::STATUS_NEAR_EXPIRY => 'yellow',
        self::STATUS_EXPIRED => 'red',
        self::STATUS_DEPLETED => 'gray',
    ];

    protected $fillable = [
        'product_id',
        'batch_number',
        'mfg_date',
        'expiry_date',
        'initial_qty',
        'current_qty',
        'unit_cost',
        'supplier_id',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'mfg_date' => 'date',
            'expiry_date' => 'date',
            'initial_qty' => 'decimal:3',
            'current_qty' => 'decimal:3',
            'unit_cost' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'batch_id')->latest();
    }

    public function isExpired(): bool
    {
        return $this->expiry_date !== null && $this->expiry_date->isPast();
    }

    public function isNearExpiry(int $days = 60): bool
    {
        if ($this->expiry_date === null) {
            return false;
        }

        return ! $this->isExpired() && $this->expiry_date->diffInDays(now(), false) >= -$days;
    }

    public function daysUntilExpiry(): ?int
    {
        if ($this->expiry_date === null) {
            return null;
        }

        return (int) round(now()->diffInDays($this->expiry_date, false));
    }

    public function refreshStatus(): void
    {
        if ((float) $this->current_qty <= 0) {
            $this->status = self::STATUS_DEPLETED;
        } elseif ($this->isExpired()) {
            $this->status = self::STATUS_EXPIRED;
        } elseif ($this->isNearExpiry()) {
            $this->status = self::STATUS_NEAR_EXPIRY;
        } else {
            $this->status = self::STATUS_ACTIVE;
        }

        $this->save();
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeExpiringSoon($query, int $days = 60)
    {
        return $query->whereNotNull('expiry_date')
            ->where('expiry_date', '>=', now()->toDateString())
            ->where('expiry_date', '<=', now()->addDays($days)->toDateString())
            ->where('current_qty', '>', 0);
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now()->toDateString())
            ->where('current_qty', '>', 0);
    }
}
