<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:roles.manage'),
        ];
    }

    public function index(): View
    {
        return view('admin.roles.index', [
            'roles' => Role::withCount('permissions', 'users')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.roles.create', [
            'permissions' => Permission::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
        ]);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($request->input('permissions', []));

        return redirect()->route('admin.roles.index')->with('status', 'role-created');
    }

    public function edit(Role $role): View
    {
        return view('admin.roles.edit', [
            'role' => $role->load('permissions'),
            'permissions' => Permission::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        abort_if($role->is_system && $request->input('name') !== $role->name, 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,'.$role->id],
        ]);

        $role->update(['name' => $data['name']]);
        $role->syncPermissions($request->input('permissions', []));

        return redirect()->route('admin.roles.index')->with('status', 'role-updated');
    }

    public function destroy(Role $role): RedirectResponse
    {
        abort_if($role->is_system, 403, 'System roles cannot be deleted.');

        $role->delete();

        return redirect()->route('admin.roles.index')->with('status', 'role-deleted');
    }
}
