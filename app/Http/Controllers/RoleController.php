<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount(['users', 'permissions'])->orderBy('name')->get();

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $permissionsByModule = Permission::orderBy('name')->get()->groupBy('module');

        return view('roles.create', compact('permissionsByModule'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => \Illuminate\Support\Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_system' => false,
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);

        return redirect()->route('roles.index')->with('success', "Role '{$role->name}' created successfully!");
    }

    public function edit(Role $role)
    {
        $permissionsByModule = Permission::orderBy('name')->get()->groupBy('module');
        $assignedPermissionIds = $role->permissions()->pluck('permissions.id')->all();

        return view('roles.edit', compact('role', 'permissionsByModule', 'assignedPermissionIds'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        // The admin role always keeps every permission - it's the superuser role.
        if ($role->slug !== 'admin') {
            $role->permissions()->sync($validated['permissions'] ?? []);
        }

        return redirect()->route('roles.index')->with('success', "Role '{$role->name}' updated successfully!");
    }

    public function destroy(Role $role)
    {
        if ($role->is_system) {
            return redirect()->route('roles.index')->with('error', 'System roles cannot be deleted.');
        }

        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')->with('error', 'Cannot delete a role that is still assigned to users.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully!');
    }
}
