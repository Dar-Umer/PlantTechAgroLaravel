<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Support\PermissionCatalog;
use Database\Seeders\AdminSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolesAndPermissionsSeeder::class, AdminSeeder::class]);
    }

    private function superAdmin(): Admin
    {
        $admin = Admin::where('email', 'admin@pta.com')->first();
        $admin->assignRole('Super Admin');
        return $admin;
    }

    public function test_super_admin_can_view_roles_index(): void
    {
        $response = $this->actingAs($this->superAdmin(), 'admin')
            ->get(route('admin.roles.index'));

        $response->assertOk();
        $response->assertSee('Roles & Permissions');
        $response->assertSee('Super Admin');
        $response->assertSee('POS & Stock Operator');
        $response->assertSee('Field Agent');
    }

    public function test_super_admin_can_create_custom_role_with_permissions(): void
    {
        $response = $this->actingAs($this->superAdmin(), 'admin')
            ->post(route('admin.roles.store'), [
                'name' => 'Quality Inspector',
                'permissions' => [
                    'work-orders.view',
                    'inventory.view',
                    'inventory.batches',
                ],
            ]);

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHas('success');

        $role = Role::where('name', 'Quality Inspector')->where('guard_name', 'admin')->first();
        $this->assertNotNull($role);
        $this->assertTrue($role->hasPermissionTo('work-orders.view', 'admin'));
        $this->assertTrue($role->hasPermissionTo('inventory.view', 'admin'));
        $this->assertTrue($role->hasPermissionTo('inventory.batches', 'admin'));
        $this->assertFalse($role->hasPermissionTo('pos.terminal', 'admin'));
    }

    public function test_super_admin_can_update_role_permissions(): void
    {
        $role = Role::create(['name' => 'Field Coordinator', 'guard_name' => 'admin']);
        $role->givePermissionTo('leads.view');

        $response = $this->actingAs($this->superAdmin(), 'admin')
            ->put(route('admin.roles.update', $role), [
                'name' => 'Senior Field Coordinator',
                'permissions' => [
                    'leads.view',
                    'leads.manage',
                    'customers.view',
                ],
            ]);

        $response->assertRedirect(route('admin.roles.index'));
        $role->refresh();

        $this->assertSame('Senior Field Coordinator', $role->name);
        $this->assertTrue($role->hasPermissionTo('leads.manage', 'admin'));
        $this->assertTrue($role->hasPermissionTo('customers.view', 'admin'));
    }

    public function test_super_admin_role_cannot_be_deleted(): void
    {
        $superAdminRole = Role::where('name', 'Super Admin')->where('guard_name', 'admin')->first();

        $response = $this->actingAs($this->superAdmin(), 'admin')
            ->delete(route('admin.roles.destroy', $superAdminRole));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('roles', ['name' => 'Super Admin', 'guard_name' => 'admin']);
    }

    public function test_role_in_use_by_staff_cannot_be_deleted(): void
    {
        $role = Role::create(['name' => 'Logistics Agent', 'guard_name' => 'admin']);
        $staff = Admin::create([
            'name' => 'Logistics User',
            'email' => 'logistics@pta.com',
            'password' => 'Password123!',
            'role' => 'Logistics Agent',
            'is_active' => true,
        ]);
        $staff->assignRole('Logistics Agent');

        $response = $this->actingAs($this->superAdmin(), 'admin')
            ->delete(route('admin.roles.destroy', $role));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('roles', ['name' => 'Logistics Agent']);
    }

    public function test_unused_custom_role_can_be_deleted(): void
    {
        $role = Role::create(['name' => 'Temporary Role', 'guard_name' => 'admin']);

        $response = $this->actingAs($this->superAdmin(), 'admin')
            ->delete(route('admin.roles.destroy', $role));

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('roles', ['name' => 'Temporary Role']);
    }

    public function test_staff_controller_allows_assigning_dynamically_created_role(): void
    {
        Role::create(['name' => 'Audit Officer', 'guard_name' => 'admin']);

        $response = $this->actingAs($this->superAdmin(), 'admin')
            ->post(route('admin.staff.store'), [
                'name' => 'John Auditor',
                'email' => 'auditor@pta.com',
                'phone' => '9876543210',
                'role' => 'Audit Officer',
                'password' => 'SecurePass#123',
                'password_confirmation' => 'SecurePass#123',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.staff.index'));
        $created = Admin::where('email', 'auditor@pta.com')->first();
        $this->assertNotNull($created);
        $this->assertTrue($created->hasRole('Audit Officer'));
    }

    public function test_unauthorized_user_cannot_access_role_management(): void
    {
        $agent = Admin::create([
            'name' => 'Basic Agent',
            'email' => 'agent@pta.com',
            'password' => 'Password123!',
            'role' => 'Field Agent',
            'is_active' => true,
        ]);
        $agent->assignRole('Field Agent');

        $response = $this->actingAs($agent, 'admin')
            ->get(route('admin.roles.index'));

        // Guarded by middleware role:Super Admin
        $response->assertForbidden();
    }
}
