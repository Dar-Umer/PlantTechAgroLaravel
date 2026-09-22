<?php

namespace App\Support;

class PermissionCatalog
{
    /**
     * Complete categorized list of permissions with user-friendly labels and descriptions.
     *
     * @return array<string, array{icon: string, permissions: array<string, array{label: string, desc: string}>}>
     */
    public static function grouped(): array
    {
        return [
            'Operations & CRM' => [
                'icon' => 'operations',
                'permissions' => [
                    'work-orders.view' => ['label' => 'View Work Orders', 'desc' => 'Browse and inspect farm project work orders.'],
                    'work-orders.manage' => ['label' => 'Manage Work Orders', 'desc' => 'Create, assign agents, update stages, and cancel work orders.'],
                    'leads.view' => ['label' => 'View Leads', 'desc' => 'Access incoming farmer leads and enquiries.'],
                    'leads.manage' => ['label' => 'Manage Leads', 'desc' => 'Add, edit, qualify, assign, and convert farmer leads.'],
                    'quotations.view' => ['label' => 'View Quotations', 'desc' => 'Browse project estimates and proforma invoices.'],
                    'quotations.manage' => ['label' => 'Manage Quotations', 'desc' => 'Create estimates, generate PDF, and approve & start work orders.'],
                    'customers.view' => ['label' => 'View Customers', 'desc' => 'View registered client profiles and transaction histories.'],
                    'customers.manage' => ['label' => 'Manage Customers', 'desc' => 'Create, edit customer profiles, and manage account details.'],
                    'invoices.view' => ['label' => 'View Invoices', 'desc' => 'Access service invoices and customer billing records.'],
                    'invoices.manage' => ['label' => 'Manage Invoices', 'desc' => 'Create invoices, record payments, and cancel invoices.'],
                ],
            ],
            'Point of Sale (POS)' => [
                'icon' => 'pos',
                'permissions' => [
                    'pos.terminal' => ['label' => 'Access POS Terminal', 'desc' => 'Operate retail billing screen, scan items, and complete sales.'],
                    'pos.sales.view' => ['label' => 'View POS Sales', 'desc' => 'View transaction history, reprint 80mm receipts & A4 tax invoices.'],
                    'pos.sales.cancel' => ['label' => 'Cancel / Refund POS Sale', 'desc' => 'Void sales and reverse stock back to active inventory lots.'],
                    'pos.customers.manage' => ['label' => 'Manage POS Customers', 'desc' => 'Quick-add and manage retail walk-in and B2B customers.'],
                ],
            ],
            'Inventory & Stock' => [
                'icon' => 'inventory',
                'permissions' => [
                    'inventory.view' => ['label' => 'View Products', 'desc' => 'Browse catalog, stock quantities, and retail pricing.'],
                    'inventory.manage' => ['label' => 'Manage Products', 'desc' => 'Create, edit products, update rates, and configure thresholds.'],
                    'inventory.stock-in' => ['label' => 'Record Stock In (Inward)', 'desc' => 'Receive supplier shipments, record unit costs, and create lots.'],
                    'inventory.stock-out' => ['label' => 'Record Stock Out & Adjustments', 'desc' => 'Record consumption, write-offs, and stock count corrections.'],
                    'inventory.batches' => ['label' => 'Batches & Expiry Tracking', 'desc' => 'Monitor batch/lot numbers, purchase costs, and shelf life.'],
                    'suppliers.manage' => ['label' => 'Manage Suppliers', 'desc' => 'Manage vendor directory, contact details, and stock alerts.'],
                ],
            ],
            'Services & Workflow' => [
                'icon' => 'services',
                'permissions' => [
                    'services.view' => ['label' => 'View Services', 'desc' => 'Browse agricultural services and package offerings.'],
                    'services.manage' => ['label' => 'Manage Services', 'desc' => 'Create and modify service packages and deliverables.'],
                    'services.stages.manage' => ['label' => 'Manage Service Stages (Kanban)', 'desc' => 'Configure stages, drag-and-drop reorder, and material templates.'],
                ],
            ],
            'CMS & Website Content' => [
                'icon' => 'content',
                'permissions' => [
                    'content.manage' => ['label' => 'Manage Website Content', 'desc' => 'Edit blog posts, apple varieties, partners, gallery, FAQs, projects.'],
                    'frontend.editor' => ['label' => 'Frontend & Notice Editor', 'desc' => 'Customize homepage hero slider, notice ticker, and SEO.'],
                ],
            ],
            'Finance & Reports' => [
                'icon' => 'reports',
                'permissions' => [
                    'reports.gst' => ['label' => 'GST & Tax Reports', 'desc' => 'Access and export GSTR-1 outward supplies reports.'],
                    'customers.ledger' => ['label' => 'Customer Ledgers', 'desc' => 'View and export account ledgers, debits, and credits.'],
                ],
            ],
            'System Administration' => [
                'icon' => 'settings',
                'permissions' => [
                    'roles.manage' => ['label' => 'Manage Roles & Permissions', 'desc' => 'Create and configure custom staff roles and access rights.'],
                    'staff.view' => ['label' => 'View Staff Accounts', 'desc' => 'View list of system administrators and staff.'],
                    'staff.manage' => ['label' => 'Manage Staff Accounts', 'desc' => 'Create, edit staff accounts, and assign roles.'],
                    'settings.manage' => ['label' => 'System & API Settings', 'desc' => 'Configure store details, weather API, SMS, SMTP, and security.'],
                    'automation.manage' => ['label' => 'Automation & Mobile Apps', 'desc' => 'Manage notification rules and mobile app integration.'],
                ],
            ],
        ];
    }

    /**
     * Flat key-value map of all permissions.
     */
    public static function all(): array
    {
        $all = [];
        foreach (static::grouped() as $group) {
            foreach ($group['permissions'] as $name => $meta) {
                $all[$name] = $meta;
            }
        }

        return $all;
    }

    /**
     * All permission names as array of strings.
     */
    public static function allNames(): array
    {
        return array_keys(static::all());
    }

    /**
     * Preset permission sets for default roles.
     */
    public static function rolePresets(): array
    {
        $all = static::allNames();

        return [
            'Super Admin' => $all,
            'Manager' => [
                'work-orders.view', 'work-orders.manage',
                'leads.view', 'leads.manage',
                'quotations.view', 'quotations.manage',
                'customers.view', 'customers.manage',
                'invoices.view', 'invoices.manage',
                'pos.terminal', 'pos.sales.view', 'pos.customers.manage',
                'inventory.view', 'inventory.manage', 'inventory.stock-in', 'inventory.stock-out', 'inventory.batches', 'suppliers.manage',
                'services.view', 'services.manage', 'services.stages.manage',
                'reports.gst', 'customers.ledger',
            ],
            'POS & Stock Operator' => [
                'pos.terminal', 'pos.sales.view', 'pos.sales.cancel', 'pos.customers.manage',
                'inventory.view', 'inventory.stock-in', 'inventory.stock-out', 'inventory.batches', 'suppliers.manage',
            ],
            'Field Agent' => [
                'work-orders.view', 'work-orders.manage',
                'leads.view', 'customers.view',
            ],
            'Accountant' => [
                'invoices.view', 'invoices.manage',
                'quotations.view',
                'pos.sales.view',
                'reports.gst', 'customers.ledger', 'customers.view',
            ],
            'Content Editor' => [
                'content.manage', 'frontend.editor',
            ],
        ];
    }

    /**
     * Synchronize permissions and default roles in the database.
     */
    public static function syncToDatabase(): void
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (static::allNames() as $permissionName) {
            \Spatie\Permission\Models\Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'admin',
            ]);
        }

        // Ensure legacy permissions exist so older queries don't fail
        $legacy = ['dashboard.view', 'settings.view', 'settings.update', 'mobile.manage'];
        foreach ($legacy as $perm) {
            \Spatie\Permission\Models\Permission::firstOrCreate([
                'name' => $perm,
                'guard_name' => 'admin',
            ]);
        }

        foreach (static::rolePresets() as $roleName => $perms) {
            $role = \Spatie\Permission\Models\Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'admin',
            ]);

            // For existing roles, sync permissions if it's Super Admin or newly created
            if ($role->wasRecentlyCreated || $roleName === 'Super Admin') {
                $role->syncPermissions($perms);
            }
        }

        // Ensure admin@pta.com has Super Admin role
        $admin = \App\Models\Admin::where('email', 'admin@pta.com')->first();
        if ($admin && ! $admin->hasRole('Super Admin')) {
            $admin->assignRole('Super Admin');
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}

