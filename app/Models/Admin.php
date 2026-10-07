<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected $guard_name = 'admin';

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'role',
        'avatar', 'is_active', 'last_login_at', 'last_login_ip',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\AdminResetPassword($token, $this->email));
    }

    public function isSuperAdmin(): bool
    {
        if (in_array(strtolower((string) $this->role), ['admin', 'super admin', 'super_admin'], true)) {
            return true;
        }

        try {
            return $this->hasRole('Super Admin');
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function isPosOnly(): bool
    {
        if (in_array($this->role, ['pos_manager', 'pos_operator', 'pos_staff', 'POS & Stock Operator'], true)) {
            return true;
        }

        try {
            if ($this->hasAnyRole(['POS Manager', 'POS Operator', 'POS & Stock Operator', 'pos_manager', 'pos_operator'])) {
                return true;
            }

            return $this->roles->contains(function ($r) {
                $name = strtolower($r->name);
                return str_contains($name, 'pos') || str_contains($name, 'cashier');
            });
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function canAccessPos(): bool
    {
        return true;
    }

    public function canAccessStock(): bool
    {
        return true;
    }

    public function assignedTickets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    public function deviceTokens(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(DeviceToken::class, 'tokenable');
    }

    public function routeNotificationForFcm(): array
    {
        return $this->deviceTokens()->pluck('token')->filter()->all();
    }
}
