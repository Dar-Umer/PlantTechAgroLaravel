<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    public function index()
    {
        $staff = Admin::with('roles')->orderBy('name')->paginate(15);

        return view('admin.staff.index', compact('staff'));
    }

    public function create()
    {
        return view('admin.staff.create', ['roles' => $this->assignableRoles()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $staff = Admin::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
            'is_active' => isset($data['is_active']),
            'email_verified_at' => now(),
        ]);

        $staff->assignRole($data['role']);

        return redirect()->route('admin.staff.index')->with('success', 'Staff member created.');
    }

    public function edit(Admin $admin)
    {
        return view('admin.staff.edit', [
            'staff' => $admin,
            'roles' => $this->assignableRoles(),
            'currentRole' => $admin->roles->first()?->name,
            'assignments' => WorkOrder::where('assigned_agent_id', $admin->id)->count(),
        ]);
    }

    public function update(Request $request, Admin $admin)
    {
        $data = $this->validated($request, $admin->id, false);

        $admin->name = $data['name'];
        $admin->email = $data['email'];
        $admin->phone = $data['phone'] ?? null;
        $admin->is_active = isset($data['is_active']);

        if (! empty($data['password'])) {
            $admin->password = $data['password'];
        }

        $admin->save();
        $admin->syncRoles([$data['role']]);

        return redirect()->route('admin.staff.index')->with('success', 'Staff member updated.');
    }

    public function destroy(Admin $admin)
    {
        if ($admin->id === auth('admin')->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($admin->hasRole('Super Admin') && Admin::role('Super Admin')->count() <= 1) {
            return back()->with('error', 'There must be at least one Super Admin.');
        }

        $admin->delete();

        return redirect()->route('admin.staff.index')->with('success', 'Staff member deleted.');
    }

    private function validated(Request $request, ?int $ignoreId = null, bool $passwordRequired = true): array
    {
        $emailRule = ['required', 'email', 'max:255'];
        $emailRule[] = $ignoreId
            ? Rule::unique('admins', 'email')->ignore($ignoreId)
            : Rule::unique('admins', 'email');

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => $emailRule,
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'role' => ['required', Rule::in(['Super Admin', 'Manager', 'Field Agent'])],
            'is_active' => ['nullable', 'boolean'],
        ];

        $rules['password'] = $passwordRequired
            ? ['required', 'string', 'min:6', 'max:64']
            : ['nullable', 'string', 'min:6', 'max:64'];

        return $request->validate($rules);
    }

    private function assignableRoles()
    {
        return Role::whereIn('name', ['Super Admin', 'Manager', 'Field Agent'])
            ->where('guard_name', 'admin')
            ->pluck('name', 'name')
            ->all();
    }
}
