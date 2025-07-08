<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    public function index()
    {
        $roles= Role::with('permissions')->get();
        return view('admin.rolePermissions.index', compact('roles'));
    }


    public function assignPermissionForm($role_id)
    {
        $role = Role::with('permissions')->findOrFail($role_id);
        $permissions = Permission::all();
        return view('admin.rolePermissions.assignPermission', compact('role', 'permissions'));
    }

    public function assignPermissions(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);
        $role = Role::findOrFail($request->role_id);
        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('view.role')->with('success', 'Permissions updated successfully!');
    }
}
