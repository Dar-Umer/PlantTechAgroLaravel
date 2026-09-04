<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'content', 'icon', 'image',
        'category', 'book_url', 'sort_order', 'is_active',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ServiceItem::class)->orderBy('sort_order');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(ServiceStage::class)->orderBy('sort_order');
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Service $service) {
            if (empty($service->slug)) {
                $service->slug = Str::slug($service->name);
            }
        });
    }
}
