<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens;
    use Notifiable;

    protected $fillable = [
        'orchardist_id', 'name', 'phone', 'gstin', 'password', 'email', 'address', 'area',
        'status', 'notes', 'lead_id', 'last_login_at', 'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Customer $customer) {
            if (empty($customer->orchardist_id)) {
                $customer->orchardist_id = static::nextOrchardistId();
            }
        });
    }

    public static function nextOrchardistId(): string
    {
        $seq = static::count() + 1001;

        for ($attempt = 0; $attempt < 50; $attempt++) {
            $id = sprintf('OID-%04d', $seq);

            if (! static::where('orchardist_id', $id)->exists()) {
                return $id;
            }

            $seq++;
        }

        return 'OID-'.uniqid();
    }

    public function orchards(): HasMany
    {
        return $this->hasMany(Orchard::class);
    }

    public function companyOrchards(): HasMany
    {
        return $this->hasMany(Orchard::class)->where('is_company_established', true);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isB2B(): bool
    {
        return ! empty($this->gstin);
    }

    public static function findByPhoneDigits(string $phone): ?self
    {
        $digits = preg_replace('/[^\d]/', '', $phone) ?? '';

        return static::whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, '+', ''), '-', ''), ' ', ''), '(', ''), ')', '') = ?", [$digits])->first();
    }
}
