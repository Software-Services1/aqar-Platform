<?php

namespace App\Http\Controllers;

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        Gate::authorize('manage-roles');

        return view('roles.index', [
            'roles'           => Role::with('permissions')->withCount('users')->orderBy('name')->get(),
            'permissions'     => Permission::orderBy('name')->get(),
            'permissionLabels' => RolePermissionSeeder::PERMISSIONS,
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('manage-roles');
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);

        return back()->with('success', 'تم إنشاء الدور.');
    }

    public function update(Request $request, Role $role)
    {
        Gate::authorize('manage-roles');
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255', "unique:roles,name,{$role->id}"],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        return back()->with('success', 'تم تحديث صلاحيات الدور.');
    }

    public function destroy(Role $role)
    {
        Gate::authorize('manage-roles');
        abort_if(in_array($role->name, ['manager', 'employee'], true), 403, 'لا يمكن حذف الأدوار الأساسية.');
        $role->delete();

        return back()->with('success', 'تم حذف الدور.');
    }
}
