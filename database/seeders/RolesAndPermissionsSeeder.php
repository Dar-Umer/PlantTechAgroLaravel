<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'dashboard.view',
            'settings.view',
            'settings.update',
            'staff.manage',
            'automation.manage',
            'mobile.manage',
            'leads.view',
            'leads.manage',
            'customers.view',
            'customers.manage',
            'work-orders.view',
            'work-orders.manage',
            'invoices.view',
            'invoices.manage',
            'inventory.view',
            'inventory.manage',
            'content.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'admin']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'admin']);
        $superAdmin->syncPermissions($permissions);

        $manager = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'admin']);
        $manager->syncPermissions([
            'dashboard.view',
            'leads.view',
            'leads.manage',
            'customers.view',
            'customers.manage',
            'work-orders.view',
            'work-orders.manage',
            'invoices.view',
            'invoices.manage',
            'inventory.view',
            'inventory.manage',
            'content.manage',
        ]);

        $agent = Role::firstOrCreate(['name' => 'Field Agent', 'guard_name' => 'admin']);
        $agent->syncPermissions([
            'dashboard.view',
            'leads.view',
            'customers.view',
            'work-orders.view',
        ]);

        $admin = Admin::where('email', 'admin@pta.com')->first();
        if ($admin && ! $admin->hasRole('Super Admin')) {
            $admin->assignRole('Super Admin');
        }
    }
}
