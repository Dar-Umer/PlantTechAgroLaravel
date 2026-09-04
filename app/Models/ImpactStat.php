<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImpactStat extends Model
{
    protected $fillable = ['label', 'value', 'suffix', 'icon', 'sort_order', 'is_active'];

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
}
