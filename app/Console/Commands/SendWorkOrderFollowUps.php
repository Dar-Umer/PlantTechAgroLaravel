<?php

namespace App\Console\Commands;

use App\Models\WorkOrder;
use App\Notifications\WorkOrderStale;
use App\Services\AdminNotifier;
use Illuminate\Console\Command;

class SendWorkOrderFollowUps extends Command
{
    protected $signature = 'work-orders:send-followups';

    protected $description = 'Remind agents/admins about stale assigned or in-progress work orders.';

    public function handle(): int
    {
        if (! config('automation.work_order_reminders_enabled', true)) {
            return self::SUCCESS;
        }

        $days = max(1, (int) config('automation.work_order_stale_days', 7));
        $cutoff = now()->subDays($days);

        $orders = WorkOrder::query()
            ->whereIn('status', ['assigned', 'in_progress'])
            ->where('updated_at', '<', $cutoff)
            ->with('agent')
            ->get();

        $notified = 0;

        foreach ($orders as $order) {
            if ($order->last_reminder_sent_at && $order->last_reminder_sent_at->gt($cutoff)) {
                continue;
            }

            $order->update(['last_reminder_sent_at' => now()]);

            if ($order->agent) {
                $order->agent->notify(new WorkOrderStale($order));
            }

            AdminNotifier::send(new WorkOrderStale($order));
            $notified++;
        }

        $this->info("Sent {$notified} stale work order reminder(s).");

        return self::SUCCESS;
    }
}