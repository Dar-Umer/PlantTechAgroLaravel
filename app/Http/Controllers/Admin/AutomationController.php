<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ShopSettingsService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AutomationController extends Controller
{
    public function index()
    {
        $settings = $this->current();

        return view('admin.automation.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'auto_invoice_on_completion' => ['sometimes', 'in:0,1,on'],
            'overdue_enabled' => ['sometimes', 'in:0,1,on'],
            'overdue_grace_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'overdue_notify_customer' => ['sometimes', 'in:0,1,on'],
            'work_order_reminders_enabled' => ['sometimes', 'in:0,1,on'],
            'work_order_stale_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'lead_escalation_enabled' => ['sometimes', 'in:0,1,on'],
            'lead_stale_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'new_lead_alerts_enabled' => ['sometimes', 'in:0,1,on'],
            'new_lead_popup_enabled' => ['sometimes', 'in:0,1,on'],
            'new_lead_popup_interval' => ['nullable', 'integer', 'min:15', 'max:300'],
            'channel' => ['required', Rule::in(['both', 'database', 'email'])],
        ]);

        $settings = $this->current();

        foreach ($validated as $key => $value) {
            if ($key === 'channel') {
                $settings['channel'] = $value;
                continue;
            }

            if (str_ends_with($key, '_days') || str_ends_with($key, '_grace_days')) {
                $settings[$key] = (int) ($value ?: 0);
                continue;
            }

            if ($key === 'new_lead_popup_interval') {
                $settings[$key] = max(15, min(300, (int) ($value ?: 60)));
                continue;
            }

            if (in_array($key, [
                'auto_invoice_on_completion',
                'overdue_enabled',
                'overdue_notify_customer',
                'work_order_reminders_enabled',
                'lead_escalation_enabled',
                'new_lead_alerts_enabled',
                'new_lead_popup_enabled',
            ], true)) {
                $settings[$key] = in_array($value, ['1', 'on'], true);
            }
        }

        app(ShopSettingsService::class)->set($settings, 'automation');

        return redirect()->route('admin.automation.index')
            ->with('success', 'Automation settings saved.');
    }

    protected function current(): array
    {
        return config('automation', []);
    }
}