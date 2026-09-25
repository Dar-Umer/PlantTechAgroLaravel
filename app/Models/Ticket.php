<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'pest_disease' => 'Pest & Disease / کیڑے اور بیماریاں',
        'irrigation' => 'Irrigation & Drip / آبپاشی',
        'fertilizer_soil' => 'Fertilizer & Soil / کھاد اور مٹی',
        'pruning_training' => 'Pruning & Training / شاخ تراشی',
        'plantation' => 'High Density Plantation / پودے لگانا',
        'billing_orders' => 'Billing & Orders / بل اور آرڈرز',
        'general' => 'General Inquiry / عام معلومات',
    ];

    public const PRIORITIES = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'urgent' => 'Urgent',
    ];

    public const STATUSES = [
        'open' => 'Open',
        'in_progress' => 'In Progress',
        'awaiting_farmer' => 'Awaiting Farmer',
        'resolved' => 'Resolved',
        'closed' => 'Closed',
    ];

    protected $fillable = [
        'ticket_number',
        'customer_id',
        'orchard_id',
        'assigned_to',
        'subject',
        'category',
        'priority',
        'status',
        'creation_mode',
        'last_reply_at',
        'last_reply_by',
        'resolved_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'last_reply_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Ticket $ticket) {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = static::generateTicketNumber();
            }
        });
    }

    public static function generateTicketNumber(): string
    {
        $prefix = 'TKT-' . date('ym');
        $latest = static::where('ticket_number', 'like', $prefix . '-%')
            ->orderBy('id', 'desc')
            ->value('ticket_number');

        if ($latest && preg_match('/(\d+)$/', $latest, $matches)) {
            $seq = (int) $matches[1] + 1;
        } else {
            $seq = 1;
        }

        for ($i = 0; $i < 50; $i++) {
            $num = sprintf('%s-%04d', $prefix, $seq);
            if (! static::where('ticket_number', $num)->exists()) {
                return $num;
            }
            $seq++;
        }

        return $prefix . '-' . uniqid();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function orchard(): BelongsTo
    {
        return $this->belongsTo(Orchard::class);
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'assigned_to');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class)->orderBy('created_at', 'asc');
    }

    public function publicMessages(): HasMany
    {
        return $this->hasMany(TicketMessage::class)->where('is_internal', false)->orderBy('created_at', 'asc');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function categoryLabel(): string
    {
        return static::CATEGORIES[$this->category] ?? ucfirst(str_replace('_', ' ', $this->category));
    }

    public function priorityLabel(): string
    {
        return static::PRIORITIES[$this->priority] ?? ucfirst($this->priority);
    }

    public function statusLabel(): string
    {
        return static::STATUSES[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function statusBadgeClasses(): string
    {
        return match ($this->status) {
            'open' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20',
            'in_progress' => 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20',
            'awaiting_farmer' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20',
            'resolved' => 'bg-purple-50 text-purple-700 ring-1 ring-purple-600/20',
            'closed' => 'bg-gray-100 text-gray-700 ring-1 ring-gray-600/20',
            default => 'bg-gray-50 text-gray-700 ring-1 ring-gray-600/20',
        };
    }

    public function priorityBadgeClasses(): string
    {
        return match ($this->priority) {
            'urgent' => 'bg-rose-50 text-rose-700 ring-1 ring-rose-600/20 font-semibold',
            'high' => 'bg-orange-50 text-orange-700 ring-1 ring-orange-600/20',
            'medium' => 'bg-sky-50 text-sky-700 ring-1 ring-sky-600/20',
            'low' => 'bg-gray-50 text-gray-600 ring-1 ring-gray-600/20',
            default => 'bg-gray-50 text-gray-600 ring-1 ring-gray-600/20',
        };
    }

    public function firstPhoto(): ?TicketAttachment
    {
        return $this->attachments()->where('file_type', 'image')->first();
    }
}
