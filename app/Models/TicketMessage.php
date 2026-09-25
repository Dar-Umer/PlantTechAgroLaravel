<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'sender_type', // 'customer', 'admin', 'system'
        'sender_id',
        'message',
        'is_internal',
    ];

    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class, 'message_id');
    }

    public function senderName(): string
    {
        if ($this->sender_type === 'customer') {
            $customer = Customer::find($this->sender_id);
            return $customer ? $customer->name : 'Farmer';
        }

        if ($this->sender_type === 'admin') {
            $admin = Admin::find($this->sender_id);
            return $admin ? $admin->name : 'Support Staff';
        }

        return 'System Notification';
    }

    public function senderRole(): string
    {
        if ($this->sender_type === 'customer') {
            return 'Farmer';
        }

        if ($this->sender_type === 'admin') {
            $admin = Admin::find($this->sender_id);
            return $admin?->role ? ucfirst(str_replace('_', ' ', $admin->role)) : 'Support Staff';
        }

        return 'System';
    }
}
