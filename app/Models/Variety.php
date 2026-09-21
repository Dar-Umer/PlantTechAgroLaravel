<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Variety extends Model
{
    protected $fillable = ['name', 'slug', 'category', 'season', 'taste', 'image', 'short_description', 'is_active', 'sort_order'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
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