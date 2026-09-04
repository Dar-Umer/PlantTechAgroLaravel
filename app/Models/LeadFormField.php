<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LeadFormField extends Model
{
    public const TYPES = [
        'text' => 'Text',
        'tel' => 'Phone Number',
        'email' => 'Email',
        'number' => 'Number',
        'date' => 'Date',
        'textarea' => 'Long Text',
        'select' => 'Dropdown',
    ];

    protected $fillable = [
        'label', 'name', 'type', 'options', 'is_required', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (LeadFormField $field) {
            if (empty($field->name)) {
                $field->name = Str::slug($field->label, '_');
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
