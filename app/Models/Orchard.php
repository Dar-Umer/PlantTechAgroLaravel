<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Orchard extends Model
{
    use HasFactory;

    protected $fillable = [
        'orchard_id',
        'customer_id',
        'work_order_id',
        'name',
        'address',
        'latitude',
        'longitude',
        'area_kanals',
        'tree_count',
        'date_of_establishment',
        'is_company_established',
        'variety_notes',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'area_kanals' => 'float',
            'tree_count' => 'integer',
            'date_of_establishment' => 'date',
            'is_company_established' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Orchard $orchard) {
            if (empty($orchard->orchard_id)) {
                $orchard->orchard_id = static::nextOrchardId();
            }
        });
    }

    public static function nextOrchardId(): string
    {
        $seq = static::count() + 1001;

        for ($attempt = 0; $attempt < 50; $attempt++) {
            $id = sprintf('ORC-%04d', $seq);

            if (! static::where('orchard_id', $id)->exists()) {
                return $id;
            }

            $seq++;
        }

        return 'ORC-'.uniqid();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function establishmentWorkOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'work_order_id');
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class)->latest();
    }

    public function getAgeAttribute(): ?string
    {
        if (! $this->date_of_establishment) {
            return null;
        }

        $established = Carbon::parse($this->date_of_establishment);
        $diff = $established->diff(now());

        if ($diff->y > 0) {
            return $diff->y.' yr'.($diff->y > 1 ? 's' : '').($diff->m > 0 ? ' '.$diff->m.' mo'.($diff->m > 1 ? 's' : '') : '');
        }

        if ($diff->m > 0) {
            return $diff->m.' mo'.($diff->m > 1 ? 's' : '');
        }

        return $diff->d.' day'.($diff->d > 1 ? 's' : '');
    }

    public function getGoogleMapsUrlAttribute(): ?string
    {
        if ($this->latitude !== null && $this->longitude !== null) {
            return 'https://maps.google.com/?q='.$this->latitude.','.$this->longitude;
        }

        return null;
    }

    public function getCompanyTagAttribute(): string
    {
        return $this->is_company_established ? 'Established by Plant Tech Agro' : 'Self Registered';
    }
}
