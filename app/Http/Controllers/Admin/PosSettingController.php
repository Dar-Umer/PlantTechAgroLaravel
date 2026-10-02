<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ShopSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PosSettingController extends Controller
{
    /**
     * Display the dedicated POS & Stock Subdomain settings page.
     */
    public function index()
    {
        $defaults = config('pos', []);

        // Load persisted settings from settings table if available
        $stored = [];
        $allSettings = app(ShopSettingsService::class)->all();
        foreach ($allSettings as $key => $val) {
            if (str_starts_with($key, 'pos.')) {
                $stored[substr($key, 4)] = $val;
            }
        }

        $settings = array_merge($defaults, $stored);

        return view('admin.settings.pos', compact('settings'));
    }

    /**
     * Update and persist POS & Store settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            // Subdomain & Deployment
            'subdomain_url' => 'required|url|max:255',
            'subdomain_enabled' => 'nullable|in:0,1',
            'force_https' => 'nullable|in:0,1',
            'kiosk_mode' => 'nullable|in:0,1',
            'cors_allowed_origins' => 'nullable|string|max:500',

            // Store Profile
            'store_name' => 'required|string|max:255',
            'store_code' => 'required|string|max:50',
            'store_tagline' => 'nullable|string|max:255',
            'store_phone' => 'nullable|string|max:50',
            'store_email' => 'nullable|email|max:255',
            'store_address' => 'nullable|string|max:500',
            'gstin' => 'nullable|string|max:50',
            'currency_symbol' => 'nullable|string|max:10',
            'operating_hours' => 'nullable|string|max:255',

            // Billing & Invoice
            'invoice_prefix' => 'required|string|max:16|regex:/^[A-Za-z0-9\-]+$/',
            'default_customer_type' => ['required', Rule::in(['walk_in', 'farmer'])],
            'allow_custom_discount' => 'nullable|in:0,1',
            'max_discount_percent' => 'nullable|numeric|min:0|max:100',
            'supervisor_pin' => 'nullable|string|max:10',
            'negative_stock_billing' => ['required', Rule::in(['block', 'warn', 'allow'])],
            'round_off_mode' => ['required', Rule::in(['nearest_rupee', 'exact'])],
            'default_tax_mode' => ['required', Rule::in(['inclusive', 'exclusive'])],

            // Payment Modes
            'enable_cash' => 'nullable|in:0,1',
            'enable_upi' => 'nullable|in:0,1',
            'enable_card' => 'nullable|in:0,1',
            'enable_bank_transfer' => 'nullable|in:0,1',
            'enable_credit_ledger' => 'nullable|in:0,1',
            'enable_split' => 'nullable|in:0,1',
            'max_farmer_credit' => 'nullable|numeric|min:0',
            'upi_vpa' => 'nullable|string|max:100',
            'upi_payee_name' => 'nullable|string|max:255',

            // Thermal Receipt
            'paper_width' => ['required', Rule::in(['80mm', '58mm', 'a4'])],
            'auto_print' => 'nullable|in:0,1',
            'show_logo' => 'nullable|in:0,1',
            'show_cashier' => 'nullable|in:0,1',
            'show_tax_summary' => 'nullable|in:0,1',
            'show_upi_qr' => 'nullable|in:0,1',
            'header_notes' => 'nullable|string|max:500',
            'footer_notes' => 'nullable|string|max:500',
            'return_policy' => 'nullable|string|max:1000',
            'logo_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',

            // Stock & Barcode
            'deduction_logic' => ['required', Rule::in(['fifo', 'lifo', 'manual'])],
            'low_stock_threshold' => 'required|integer|min:0',
            'critical_stock_threshold' => 'required|integer|min:0',
            'expiry_warning_days' => 'required|integer|min:0|max:365',
            'auto_increment_on_scan' => 'nullable|in:0,1',
            'scanner_sound' => 'nullable|in:0,1',

            // Cashier Shift & Security
            'require_opening_float' => 'nullable|in:0,1',
            'require_closing_reconciliation' => 'nullable|in:0,1',
            'auto_logout_minutes' => 'nullable|integer|min:0|max:480',
        ]);

        $currentConfig = config('pos', []);

        // Booleans list
        $booleanKeys = [
            'subdomain_enabled', 'force_https', 'kiosk_mode',
            'allow_custom_discount',
            'enable_cash', 'enable_upi', 'enable_card', 'enable_bank_transfer', 'enable_credit_ledger', 'enable_split',
            'auto_print', 'show_logo', 'show_cashier', 'show_tax_summary', 'show_upi_qr',
            'auto_increment_on_scan', 'scanner_sound',
            'require_opening_float', 'require_closing_reconciliation',
        ];

        $posSettings = [];

        foreach ($validated as $key => $val) {
            if ($key === 'logo_file') {
                continue;
            }
            if (in_array($key, $booleanKeys, true)) {
                $posSettings[$key] = ($val === '1' || $val === true);
            } else {
                $posSettings[$key] = $val;
            }
        }

        // For unchecked checkboxes (not present in $validated)
        foreach ($booleanKeys as $bKey) {
            if (! isset($validated[$bKey])) {
                $posSettings[$bKey] = $request->has($bKey) && $request->input($bKey) === '1';
            }
        }

        // Handle POS Logo Upload / Removal
        if ($request->hasFile('logo_file')) {
            $file = $request->file('logo_file');
            $path = $file->store('pos', 'public');
            $posSettings['logo_url'] = '/storage/' . $path;
        } elseif ($request->input('remove_logo') === '1') {
            $posSettings['logo_url'] = '';
        } elseif (isset($currentConfig['logo_url'])) {
            $posSettings['logo_url'] = $currentConfig['logo_url'];
        }

        // Persist to settings table under 'pos' namespace
        app(ShopSettingsService::class)->set($posSettings, 'pos');

        return redirect()->route('admin.settings.pos')
            ->with('success', 'Point of Sale (POS) and Store settings updated successfully.');
    }
}
