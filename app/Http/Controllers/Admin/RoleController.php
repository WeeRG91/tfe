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
        $this->authorize('viewAny', Role::class);

        return Inertia::render('admin/role/Index');
    }

    public function getRoles(Request $request)
    {
        $this->authorize('viewAny', Role::class);

        $roles = Role::with('permissions')
            ->whereNot('name', 'Super Admin')
            ->when($request->search, fn ($query) =>
                $query->where('name', 'LIKE', "%$request->search%")
            )
            ->orderBy('name')
            ->cursorPaginate(8);

        return response()->json([
            'data' => RoleResource::collection($roles)->collection,
            'path' => $roles->path(),
            'per_page' => $roles->perPage(),
            'next_cursor' => $roles->nextCursor()?->encode(),
            'next_page_url' => $roles->nextPageUrl(),
            'prev_cursor' => $roles->previousCursor()?->encode(),
            'prev_page_url' => $roles->previousPageUrl(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Role::class);

        return Inertia::render('admin/role/Create', [
            'permissions' => PermissionResource::collection(Permission::all())->collection,
        ]);
    }

    public function store(CreateRoleRequest $request)
    {
        $this->authorize('create', Role::class);

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
        $this->authorize('update', $role);

        $role->load('permissions');

        return Inertia::render('admin/role/Edit', [
            'roleToEdit' => new RoleResource($role),
            'permissions' => PermissionResource::collection(Permission::all())->collection,
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
       $this->authorize('update', $role);

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
       $this->authorize('delete', $role);

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
