<?php

namespace App\Events;

use App\Models\Lead;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired the instant a lead is booked on the landing form so open admin
 * tabs pop up immediately. ShouldBroadcastNow: pushed synchronously,
 * no queue worker required — only the Reverb server must be running.
 */
class NewLeadReceived implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Lead $lead) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('admin.leads');
    }

    public function broadcastAs(): string
    {
        return 'lead.received';
    }

    public function broadcastWith(): array
    {
        return [
            'lead_id' => $this->lead->id,
            'name' => $this->lead->name,
            'phone' => $this->lead->phone,
            'service' => $this->lead->service?->name,
            'url' => route('admin.leads.show', $this->lead),
        ];
    }
}
