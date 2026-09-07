<?php

namespace App\Console\Commands;

use App\Mail\OverdueInvoiceMail;
use App\Models\Invoice;
use App\Notifications\WorkOrderOverdue;
use App\Services\AdminNotifier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ProcessOverdueInvoices extends Command
{
    protected $signature = 'invoices:process-overdue';

    protected $description = 'Mark unpaid/partial invoices as overdue and notify admins.';

    public function handle(): int
    {
        if (! config('automation.overdue_enabled', true)) {
            return self::SUCCESS;
        }

        $grace = max(0, (int) config('automation.overdue_grace_days', 0));
        $cutoff = now()->subDays($grace)->startOfDay();

        $invoices = Invoice::query()
            ->whereIn('status', ['unpaid', 'partial'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', $cutoff)
            ->with('customer')
            ->get();

        $processed = 0;

        foreach ($invoices as $invoice) {
            if ($invoice->balanceDue() <= 0) {
                continue;
            }

            $invoice->update(['status' => 'overdue']);
            $processed++;

            AdminNotifier::send(new WorkOrderOverdue($invoice->refresh()));

            if (config('automation.overdue_notify_customer', true) && $invoice->customer?->email) {
                Mail::to($invoice->customer->email)->send(new OverdueInvoiceMail($invoice));
            }
        }

        $this->info("Marked {$processed} invoice(s) as overdue.");

        return self::SUCCESS;
    }
}