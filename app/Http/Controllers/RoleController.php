<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::all();
        $roles = Role::all();

        return view('roles.create', compact('permissions', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'required|string|max:255',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
            'assignable_roles' => 'array',
            'assignable_roles.*' => 'exists:roles,id',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
        ]);

        if (!empty($validated['permissions'])) {
            $role->permissions()->attach($validated['permissions']);
        }

        if (!empty($validated['assignable_roles'])) {
            $role->assignableRoles()->attach($validated['assignable_roles']);
        }

        return redirect()->route('roles.index')->with('status', 'Role created successfully.');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::all();
        $rolePermissionIds = $role->permissions->pluck('id')->toArray();
        $roles = Role::all();
        $assignableRoleIds = $role->assignableRoles->pluck('id')->toArray();

        return view('roles.edit', compact('role', 'permissions', 'rolePermissionIds', 'roles', 'assignableRoleIds'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'required|string|max:255',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
            'assignable_roles' => 'array',
            'assignable_roles.*' => 'exists:roles,id',
        ]);

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);
        $role->assignableRoles()->sync($validated['assignable_roles'] ?? []);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Role updated successfully.']);
        }

        return redirect()->route('roles.index')->with('status', 'Role updated successfully.');
    }

    public function copy(Role $role)
    {
        $copy = $role->duplicate();

        return redirect()->route('roles.edit', $copy)->with('status', 'Role copied successfully.');
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return redirect()->route('roles.index')->with('status', 'Role deleted successfully.');
    }
}
