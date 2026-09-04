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
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'admin']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'admin']);
        $superAdmin->syncPermissions($permissions);

        Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'admin']);
        Role::firstOrCreate(['name' => 'Field Agent', 'guard_name' => 'admin']);

        $admin = Admin::where('email', 'admin@pta.com')->first();
        if ($admin && ! $admin->hasRole('Super Admin')) {
            $admin->assignRole('Super Admin');
        }
    }
}
