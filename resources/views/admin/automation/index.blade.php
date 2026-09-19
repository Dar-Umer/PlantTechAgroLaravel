@extends('admin.layout')

@section('page-title', 'Automation')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Automation</h2>
        </div>

        <form action="{{ route('admin.automation.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Notification Delivery</h3>
                <p class="text-sm text-gray-500 mb-5">How admins and agents receive automation alerts. In-app notifications always appear in the admin bell; email delivery requires SMTP to be configured under <a href="{{ route('admin.settings.index', ['tab' => 'smtp']) }}" class="text-brand-600 underline">Settings → Email / SMTP</a>.</p>

                <x-admin.select name="channel" label="Delivery Channel"
                    :options="[
                        'both' => 'In-app + Email',
                        'database' => 'In-app only',
                        'email' => 'Email only',
                    ]"
                    :value="old('channel', $settings['channel'] ?? 'both')" />
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Invoice Automation</h3>
                <p class="text-sm text-gray-500 mb-5">Invoices are produced from the materials recorded on work order stages.</p>

                <div class="space-y-4">
                    <x-admin.checkbox name="auto_invoice_on_completion" label="Auto-generate invoice when a work order is completed"
                        :checked="$settings['auto_invoice_on_completion'] ?? true"
                        help="Creates an invoice the moment all stages of a work order are marked complete." />

                    <x-admin.checkbox name="overdue_enabled" label="Track overdue invoices"
                        :checked="$settings['overdue_enabled'] ?? true"
                        help="Marks unpaid/partial invoices as overdue after grace days and alerts admins." />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pl-7">
                        <x-admin.input name="overdue_grace_days" label="Overdue Grace (days)"
                            type="number" min="0" value="{{ $settings['overdue_grace_days'] ?? 0 }}"
                            helptext="Days past the due date before an invoice is flagged overdue." />
                    </div>

                    <x-admin.checkbox name="overdue_notify_customer" label="Email the customer when an invoice becomes overdue"
                        :checked="$settings['overdue_notify_customer'] ?? true"
                        help="Sends a friendly reminder to the customer's email address." />
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Work Order Follow-ups</h3>
                <p class="text-sm text-gray-500 mb-5">Remind agents and admins about work orders that have gone quiet.</p>

                <div class="space-y-4">
                    <x-admin.checkbox name="work_order_reminders_enabled" label="Enable stale work order reminders"
                        :checked="$settings['work_order_reminders_enabled'] ?? true" />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pl-7">
                        <x-admin.input name="work_order_stale_days" label="Stale After (days)"
                            type="number" min="1" value="{{ $settings['work_order_stale_days'] ?? 7 }}"
                            helptext="Remind if a work order has had no activity for this many days." />
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Lead Management</h3>
                <p class="text-sm text-gray-500 mb-5">Follow up on leads that are not being pursued, and get pinged on new enquiries.</p>

                <div class="space-y-4">
                    <x-admin.checkbox name="lead_escalation_enabled" label="Enable stale lead escalation"
                        :checked="$settings['lead_escalation_enabled'] ?? true"
                        help="Notify admins about open leads that have not been touched recently." />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pl-7">
                        <x-admin.input name="lead_stale_days" label="Stale After (days)"
                            type="number" min="1" value="{{ $settings['lead_stale_days'] ?? 3 }}"
                            helptext="Flag open leads with no activity for this many days." />
                    </div>

                    <x-admin.checkbox name="new_lead_alerts_enabled" label="Alert admins on every new lead"
                        :checked="$settings['new_lead_alerts_enabled'] ?? true"
                        help="Sends a notification whenever a lead submits the landing page form." />

                    <x-admin.checkbox name="new_lead_popup_enabled" label="Pop up new leads on screen with a tone"
                        :checked="$settings['new_lead_popup_enabled'] ?? true"
                        help="Shows a blocking popup and plays a chime in open admin tabs." />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pl-7">
                        <x-admin.input name="new_lead_popup_interval" label="Popup Check (seconds)"
                            type="number" min="15" value="{{ $settings['new_lead_popup_interval'] ?? 60 }}"
                            helptext="How often open admin tabs check for new leads (15–300)." />
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <x-admin.button type="submit">Save Automation Settings</x-admin.button>
            </div>
        </form>

        {{-- Server Cron Setup Guide --}}
        <div class="bg-gradient-to-br from-gray-50 to-white rounded-2xl shadow-sm border border-gray-200 p-6" x-data="{ open: true }">
            <button @click="open = !open" class="flex items-center justify-between w-full text-left group">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-gray-100 text-gray-600 rounded-xl flex items-center justify-center group-hover:bg-brand-50 group-hover:text-brand-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Server Cron Job Setup</h3>
                        <p class="text-sm text-gray-500">Required for scheduled automation to run on the live server.</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-cloak class="mt-5 space-y-5">

                <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
                    <p class="text-sm text-amber-800"><span class="font-semibold">Important:</span> Add the cron line below on the production server. Without it, overdue tracking, follow-up reminders, and lead escalations will not run automatically.</p>
                </div>

                {{-- Cron command block --}}
                <div x-data="{ copied: false }">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Cron Line</label>
                    <div class="flex items-stretch gap-2">
                        <div class="flex-1 bg-gray-900 text-gray-100 font-mono text-sm rounded-xl px-4 py-3 overflow-x-auto select-all whitespace-nowrap">
                            <span id="cron-line">* * * * * cd {{ base_path() }} && php artisan schedule:run >> /dev/null 2>&1</span>
                        </div>
                        <button type="button"
                            class="px-3 rounded-xl bg-gray-100 text-gray-600 text-xs font-medium hover:bg-gray-200 transition flex items-center gap-1.5 flex-shrink-0"
                            x-on:click="navigator.clipboard.writeText(document.getElementById('cron-line').textContent); copied = true; setTimeout(() => copied = false, 2000)">
                            <template x-if="!copied">
                                <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>Copy</span>
                            </template>
                            <template x-if="copied">
                                <span class="flex items-center gap-1.5 text-green-600"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Copied!</span>
                        </button>
                    </div>
                </div>

                {{-- Step-by-step instructions --}}
                <div class="space-y-3">
                    <h4 class="text-sm font-semibold text-gray-700">How to set it up</h4>

                    <div class="flex gap-3 items-start">
                        <span class="flex-shrink-0 w-6 h-6 bg-brand-100 text-brand-700 rounded-full flex items-center justify-center text-xs font-bold mt-0.5">1</span>
                        <p class="text-sm text-gray-600">SSH into your production server.</p>
                    </div>
                    <div class="flex gap-3 items-start">
                        <span class="flex-shrink-0 w-6 h-6 bg-brand-100 text-brand-700 rounded-full flex items-center justify-center text-xs font-bold mt-0.5">2</span>
                        <p class="text-sm text-gray-600">Open the crontab editor:<br><code class="bg-gray-100 text-gray-800 px-2 py-0.5 rounded text-xs font-mono">crontab -e</code></p>
                    </div>
                    <div class="flex gap-3 items-start">
                        <span class="flex-shrink-0 w-6 h-6 bg-brand-100 text-brand-700 rounded-full flex items-center justify-center text-xs font-bold mt-0.5">3</span>
                        <p class="text-sm text-gray-600">Paste the cron line above at the bottom of the file and save.</p>
                    </div>
                    <div class="flex gap-3 items-start">
                        <span class="flex-shrink-0 w-6 h-6 bg-brand-100 text-brand-700 rounded-full flex items-center justify-center text-xs font-bold mt-0.5">4</span>
                        <p class="text-sm text-gray-600">Verify it's active:<br><code class="bg-gray-100 text-gray-800 px-2 py-0.5 rounded text-xs font-mono">crontab -l</code></p>
                    </div>
                </div>

                {{-- What runs and when --}}
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">What this cron does</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="bg-white border border-gray-100 rounded-xl px-4 py-3">
                            <p class="text-xs font-medium text-gray-500 mb-1">Invoices: overdue check</p>
                            <p class="text-xs text-gray-400">Runs daily at 12:15 AM. Flags overdue invoices and emails customers if enabled.</p>
                        </div>
                        <div class="bg-white border border-gray-100 rounded-xl px-4 py-3">
                            <p class="text-xs font-medium text-gray-500 mb-1">Work Orders: follow-ups</p>
                            <p class="text-xs text-gray-400">Runs daily at 8:00 AM. Reminds agents about stale assigned work orders.</p>
                        </div>
                        <div class="bg-white border border-gray-100 rounded-xl px-4 py-3">
                            <p class="text-xs font-medium text-gray-500 mb-1">Leads: escalation</p>
                            <p class="text-xs text-gray-400">Runs daily at 8:05 AM. Flags open leads that need follow-up.</p>
                        </div>
                    </div>
                </div>

                <p class="text-xs text-gray-400">These settings are shared with your existing config file at <code class="bg-gray-100 px-1.5 py-0.5 rounded">config/automation.php</code>. Changes here are saved to the database and take effect immediately.</p>
            </div>
        </div>
    </div>
@endsection
