<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Role\CreateRoleRequest;
use App\Http\Requests\Admin\Role\UpdateRoleRequest;
use App\Http\Resources\Admin\Permission\PermissionResource;
use App\Http\Resources\Admin\Role\RoleResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Throwable;

class RoleController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/role/Index');
    }

    public function getRoles(Request $request)
    {
        $roles = Role::with('permissions')
            ->whereNot('name', 'Super Admin')
            ->when($request->search, fn ($query) =>
                $query->where('name', 'LIKE', "%$request->search%")
            )
            ->get();

        return response()->json(RoleResource::collection($roles));
    }

    public function create()
    {
        return Inertia::render('admin/role/Create', [
            'permissions' => PermissionResource::collection(Permission::all())->collection,
        ]);
    }

    public function store(CreateRoleRequest $request)
    {
        try {
            $role = Role::create([
                'name' => $request->name,
                'guard_name' => 'web'
            ]);

            $role->syncPermissions($request->permissions);

            return redirect()->route('admin.role.index');
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors($e->getMessage());
        }
    }

    public function edit(Role $role)
    {
        $role->load('permissions');

        return Inertia::render('admin/role/Edit', [
            'roleToEdit' => new RoleResource($role),
            'permissions' => PermissionResource::collection(Permission::all())->collection,
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
       try {
           $role->update([
               'name' => $request->name,
           ]);

           $role->syncPermissions($request->permissions);

           return redirect()->route('admin.role.index');
       } catch (Throwable $e) {
           report($e);

           return back()->withErrors($e->getMessage());
       }
    }

    public function destroy(Role $role)
    {
       try {
           if ($role->name == 'Admin') {
               return response()->json([
                   'message' => "You can't delete admin role",
               ]);
           }

           $role->permissions()->detach();
           $role->delete();

           return response()->json([
               'message' => "Role deleted successfully",
           ]);
       } catch (Throwable $e) {
           report($e);

           return back()->withErrors($e->getMessage());
       }
    }
}
