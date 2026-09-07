<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Notifications\LeadStale;
use App\Services\AdminNotifier;
use Illuminate\Console\Command;

class EscalateLeads extends Command
{
    protected $signature = 'leads:escalate';

    protected $description = 'Escalate open leads that have not been touched in N days.';

    public function handle(): int
    {
        if (! config('automation.lead_escalation_enabled', true)) {
            return self::SUCCESS;
        }

        $days = max(1, (int) config('automation.lead_stale_days', 3));
        $cutoff = now()->subDays($days);

        $leads = Lead::query()
            ->whereIn('status', ['new', 'contacted', 'no_answer', 'interested'])
            ->where('updated_at', '<', $cutoff)
            ->get();

        $notified = 0;

        foreach ($leads as $lead) {
            if ($lead->last_reminder_sent_at && $lead->last_reminder_sent_at->gt($cutoff)) {
                continue;
            }

            $lead->update(['last_reminder_sent_at' => now()]);

            AdminNotifier::send(new LeadStale($lead));
            $notified++;
        }

        $this->info("Sent {$notified} lead escalation reminder(s).");

        return self::SUCCESS;
    }
}