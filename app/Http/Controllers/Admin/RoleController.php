<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Support\PermissionCatalog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    public function __construct()
    {
        // Require authenticated admin and proper authorization
    }

    public function index()
    {
        $this->ensureAuthorized();

        $roles = Role::where('guard_name', 'admin')
            ->withCount('permissions')
            ->orderBy('name')
            ->get()
            ->map(function ($role) {
                $role->users_count = Admin::role($role->name)->count();
                $role->is_super = ($role->name === 'Super Admin');
                return $role;
            });

        $totalPermissions = Permission::where('guard_name', 'admin')->count();

        return view('admin.roles.index', compact('roles', 'totalPermissions'));
    }

    public function create()
    {
        $this->ensureAuthorized();

        $role = new Role();
        $groupedPermissions = PermissionCatalog::grouped();
        $selectedPermissions = [];
        $isEdit = false;

        return view('admin.roles.form', compact('role', 'groupedPermissions', 'selectedPermissions', 'isEdit'));
    }

    public function store(Request $request)
    {
        $this->ensureAuthorized();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'name')->where('guard_name', 'admin'),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')->where('guard_name', 'admin')],
        ]);

        $role = Role::create([
            'name' => trim($validated['name']),
            'guard_name' => 'admin',
        ]);

        if (! empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')->with('success', "Role '{$role->name}' created successfully.");
    }

    public function edit(Role $role)
    {
        $this->ensureAuthorized();

        if ($role->guard_name !== 'admin') {
            abort(404);
        }

        $groupedPermissions = PermissionCatalog::grouped();
        $selectedPermissions = $role->permissions->pluck('name')->toArray();
        $isEdit = true;

        return view('admin.roles.form', compact('role', 'groupedPermissions', 'selectedPermissions', 'isEdit'));
    }

    public function update(Request $request, Role $role)
    {
        $this->ensureAuthorized();

        if ($role->guard_name !== 'admin') {
            abort(404);
        }

        if ($role->name === 'Super Admin') {
            // Super Admin must always retain all permissions
            $role->syncPermissions(PermissionCatalog::allNames());
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            return redirect()->route('admin.roles.index')->with('success', "Super Admin role always possesses all system permissions.");
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'name')->ignore($role->id)->where('guard_name', 'admin'),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')->where('guard_name', 'admin')],
        ]);

        $role->name = trim($validated['name']);
        $role->save();

        $role->syncPermissions($validated['permissions'] ?? []);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')->with('success', "Role '{$role->name}' updated successfully.");
    }

    public function destroy(Role $role)
    {
        $this->ensureAuthorized();

        if ($role->name === 'Super Admin') {
            return back()->with('error', 'The Super Admin role is protected and cannot be deleted.');
        }

        $assignedCount = Admin::role($role->name)->count();
        if ($assignedCount > 0) {
            return back()->with('error', "Cannot delete role '{$role->name}' because it is assigned to {$assignedCount} staff member(s). Reassign them first.");
        }

        $name = $role->name;
        $role->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')->with('success', "Role '{$name}' deleted successfully.");
    }

    private function ensureAuthorized(): void
    {
        $admin = auth('admin')->user();
        if (! $admin) {
            abort(403, 'Unauthorized.');
        }

        if ($admin->hasRole('Super Admin')) {
            return;
        }

        try {
            if ($admin->hasPermissionTo('roles.manage', 'admin')) {
                return;
            }
        } catch (\Throwable $e) {
            // Permission not assigned
        }

        abort(403, 'You do not have permission to manage roles and permissions.');
    }
}
