<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordOtp extends Model
{
    protected $fillable = [
        'phone', 'code', 'code_hash', 'purpose', 'expires_at', 'consumed_at',
    ];

    protected $hidden = [
        'code', 'code_hash',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }

    public function isValid(): bool
    {
        return $this->consumed_at === null && $this->expires_at->isFuture();
    }
}