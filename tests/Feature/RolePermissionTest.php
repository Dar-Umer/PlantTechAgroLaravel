<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Support\PermissionCatalog;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        PermissionCatalog::syncToDatabase(force: true);
    }

    public function test_super_admin_has_unrestricted_access(): void
    {
        $superAdmin = Admin::create([
            'name' => 'Super Admin',
            'email' => 'super@pta.com',
            'password' => bcrypt('password'),
            'role' => 'Super Admin',
            'is_active' => true,
        ]);
        $superAdmin->assignRole('Super Admin');

        $this->actingAs($superAdmin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk();

        $this->actingAs($superAdmin, 'admin')
            ->get(route('admin.staff.index'))
            ->assertOk();

        $this->actingAs($superAdmin, 'admin')
            ->get(route('admin.roles.index'))
            ->assertOk();

        $this->actingAs($superAdmin, 'admin')
            ->get(route('admin.work-orders.index'))
            ->assertOk();

        $this->actingAs($superAdmin, 'admin')
            ->get(route('admin.invoices.index'))
            ->assertOk();
    }

    public function test_manager_cannot_access_staff_or_roles_management(): void
    {
        $manager = Admin::create([
            'name' => 'Manager User',
            'email' => 'manager@pta.com',
            'password' => bcrypt('password'),
            'role' => 'Manager',
            'is_active' => true,
        ]);
        $manager->assignRole('Manager');

        // Manager CAN access operations
        $this->actingAs($manager, 'admin')
            ->get(route('admin.work-orders.index'))
            ->assertOk();

        $this->actingAs($manager, 'admin')
            ->get(route('admin.invoices.index'))
            ->assertOk();

        // Manager CANNOT access staff management or roles (Super Admin only)
        $this->actingAs($manager, 'admin')
            ->get(route('admin.staff.index'))
            ->assertForbidden();

        $this->actingAs($manager, 'admin')
            ->get(route('admin.roles.index'))
            ->assertForbidden();
    }

    public function test_field_agent_access_control(): void
    {
        $agent = Admin::create([
            'name' => 'Field Agent User',
            'email' => 'agent@pta.com',
            'password' => bcrypt('password'),
            'role' => 'Field Agent',
            'is_active' => true,
        ]);
        $agent->assignRole('Field Agent');

        // Field Agent CAN access work orders and leads
        $this->actingAs($agent, 'admin')
            ->get(route('admin.work-orders.index'))
            ->assertOk();

        $this->actingAs($agent, 'admin')
            ->get(route('admin.leads.index'))
            ->assertOk();

        // Field Agent CANNOT access invoices, POS, or settings
        $this->actingAs($agent, 'admin')
            ->get(route('admin.invoices.index'))
            ->assertForbidden();

        $this->actingAs($agent, 'admin')
            ->get(route('admin.pos.terminal'))
            ->assertForbidden();

        $this->actingAs($agent, 'admin')
            ->get(route('admin.staff.index'))
            ->assertForbidden();
    }

    public function test_pos_operator_access_control(): void
    {
        $posUser = Admin::create([
            'name' => 'POS Cashier',
            'email' => 'cashier@pta.com',
            'password' => bcrypt('password'),
            'role' => 'POS & Stock Operator',
            'is_active' => true,
        ]);
        $posUser->assignRole('POS & Stock Operator');

        // POS Operator CAN access POS terminal and POS sales
        $this->actingAs($posUser, 'admin')
            ->get(route('admin.pos.terminal'))
            ->assertOk();

        $this->actingAs($posUser, 'admin')
            ->get(route('admin.pos.sales'))
            ->assertOk();

        // POS Operator CAN access inventory
        $this->actingAs($posUser, 'admin')
            ->get(route('admin.products.index'))
            ->assertOk();

        // POS Operator CANNOT access work orders, support tickets, or staff
        $this->actingAs($posUser, 'admin')
            ->get(route('admin.work-orders.index'))
            ->assertRedirect(route('pos.terminal'));
    }

    public function test_content_editor_access_control(): void
    {
        $editor = Admin::create([
            'name' => 'Content Writer',
            'email' => 'editor@pta.com',
            'password' => bcrypt('password'),
            'role' => 'Content Editor',
            'is_active' => true,
        ]);
        $editor->assignRole('Content Editor');

        // Content Editor CAN access blog posts & frontend editor
        $this->actingAs($editor, 'admin')
            ->get(route('admin.posts.index'))
            ->assertOk();

        $this->actingAs($editor, 'admin')
            ->get(route('admin.frontend.index'))
            ->assertOk();

        // Content Editor CANNOT access work orders, invoices, or POS
        $this->actingAs($editor, 'admin')
            ->get(route('admin.work-orders.index'))
            ->assertForbidden();

        $this->actingAs($editor, 'admin')
            ->get(route('admin.invoices.index'))
            ->assertForbidden();

        $this->actingAs($editor, 'admin')
            ->get(route('admin.pos.terminal'))
            ->assertForbidden();
    }
}
