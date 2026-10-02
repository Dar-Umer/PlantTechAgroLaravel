<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Subdomain & Host Deployment Settings
    |--------------------------------------------------------------------------
    */
    'subdomain_url' => env('POS_SUBDOMAIN_URL', 'https://pos.planttechagro.com'),
    'subdomain_enabled' => true,
    'force_https' => true,
    'kiosk_mode' => true,
    'cors_allowed_origins' => env('POS_CORS_ORIGINS', 'https://pos.planttechagro.com,http://localhost:8000'),

    /*
    |--------------------------------------------------------------------------
    | Store Profile & Physical Outlet
    |--------------------------------------------------------------------------
    */
    'store_name' => 'Plant Tech Agro',
    'store_code' => 'PTA-SRX-01',
    'store_tagline' => 'Retail Outlet & Farmers Agro Center',
    'store_phone' => '0194-796-1490',
    'store_email' => 'pos@planttechagro.com',
    'store_address' => '56 Murad House, Pine Lane-8, Kurso Rajbagh, Srinagar-190008, Jammu & Kashmir',
    'gstin' => '01AAACP9281G1Z7',
    'currency_symbol' => '₹',
    'operating_hours' => 'Mon – Sat: 9:00 AM – 7:30 PM',

    /*
    |--------------------------------------------------------------------------
    | Billing, Invoice & Cashier Discounts
    |--------------------------------------------------------------------------
    */
    'invoice_prefix' => 'POS',
    'default_customer_type' => 'walk_in',
    'allow_custom_discount' => true,
    'max_discount_percent' => 15,
    'supervisor_pin' => '1234',
    'negative_stock_billing' => 'warn', // 'block', 'warn', 'allow'
    'round_off_mode' => 'nearest_rupee', // 'nearest_rupee', 'exact'
    'default_tax_mode' => 'inclusive', // 'inclusive', 'exclusive'

    /*
    |--------------------------------------------------------------------------
    | Payment Modes & Digital Tender
    |--------------------------------------------------------------------------
    */
    'enable_cash' => true,
    'enable_upi' => true,
    'enable_card' => true,
    'enable_bank_transfer' => true,
    'enable_credit_ledger' => true,
    'enable_split' => true,
    'max_farmer_credit' => 50000,
    'upi_vpa' => 'planttechagro@jkb',
    'upi_payee_name' => 'Plant Tech Agro',

    /*
    |--------------------------------------------------------------------------
    | Thermal Receipt & Printing Settings
    |--------------------------------------------------------------------------
    */
    'paper_width' => '80mm', // '80mm', '58mm', 'a4'
    'auto_print' => true,
    'show_logo' => true,
    'show_cashier' => true,
    'show_tax_summary' => true,
    'show_upi_qr' => true,
    'header_notes' => 'Modern Orchard & Precision Agriculture Inputs',
    'footer_notes' => 'Thank you for shopping with Plant Tech Agro! Grow better with certified agri-inputs.',
    'return_policy' => 'Goods can be exchanged within 7 days with original invoice. No refund on open chemical/fertilizer packs.',
    'logo_url' => '',

    /*
    |--------------------------------------------------------------------------
    | Stock, Barcodes & Batch Policies
    |--------------------------------------------------------------------------
    */
    'deduction_logic' => 'fifo', // 'fifo', 'lifo', 'manual'
    'low_stock_threshold' => 10,
    'critical_stock_threshold' => 3,
    'expiry_warning_days' => 60,
    'auto_increment_on_scan' => true,
    'scanner_sound' => true,

    /*
    |--------------------------------------------------------------------------
    | Cashier Terminal & Shift Controls
    |--------------------------------------------------------------------------
    */
    'require_opening_float' => true,
    'require_closing_reconciliation' => true,
    'auto_logout_minutes' => 30,
];
