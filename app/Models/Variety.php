<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Variety extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'category',
        'season',
        'taste',
        'origin',
        'color',
        'storage_life',
        'image',
        'short_description',
        'description',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Variety $variety) {
            if (empty($variety->slug)) {
                $variety->slug = Str::slug($variety->name);
            }
        });
    }
}