<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\Roles\Commands\CreateRole;
use App\Actions\Admin\Roles\Commands\DeleteRole;
use App\Actions\Admin\Roles\Commands\UpdateRole;
use App\Actions\Admin\Roles\Queries\GetRoles;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Role\CreateRoleRequest;
use App\Http\Requests\Admin\Role\UpdateRoleRequest;
use App\Http\Resources\Admin\Permission\PermissionResource;
use App\Http\Resources\Admin\Role\RoleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Throwable;

class RoleController extends Controller
{
    /**
     * @return Response
     */
    public function index(): Response
    {
        $this->authorize('viewAny', Role::class);

        return Inertia::render('admin/role/Index');
    }

    /**
     * @param Request $request
     * @param GetRoles $getRoles
     * @return JsonResponse
     */
    public function getRoles(Request $request, GetRoles $getRoles): JsonResponse
    {
        $this->authorize('viewAny', Role::class);

        $roles = $getRoles->execute(
            $request->input('search')
        );

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

    /**
     * @return Response
     */
    public function create(): Response
    {
        $this->authorize('create', Role::class);

        return Inertia::render('admin/role/Create', [
            'permissions' => PermissionResource::collection(Permission::all())->collection,
        ]);
    }

    /**
     * @param CreateRoleRequest $request
     * @param CreateRole $createRole
     * @return RedirectResponse
     */
    public function store(CreateRoleRequest $request, CreateRole $createRole): RedirectResponse
    {
        $this->authorize('create', Role::class);

        try {
            $createRole->execute(
                $request->name,
                $request->permissions ?? []
            );

            return redirect()->route('admin.role.index');
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors($e->getMessage());
        }
    }

    /**
     * @param Role $role
     * @return Response
     */
    public function edit(Role $role): Response
    {
        $this->authorize('update', $role);

        $role->load('permissions');

        return Inertia::render('admin/role/Edit', [
            'roleToEdit' => new RoleResource($role),
            'permissions' => PermissionResource::collection(Permission::all())->collection,
        ]);
    }

    /**
     * @param UpdateRoleRequest $request
     * @param Role $role
     * @param UpdateRole $updateRole
     * @return RedirectResponse
     */
    public function update(
        UpdateRoleRequest $request,
        Role $role,
        UpdateRole $updateRole
    ): RedirectResponse
    {
       $this->authorize('update', $role);

       try {
           $updateRole->execute(
               $role,
               $request->name,
               $request->permissions ?? []
           );

           return redirect()->route('admin.role.index');
       } catch (Throwable $e) {
           report($e);

           return back()->withErrors($e->getMessage());
       }
    }

    /**
     * @param Role $role
     * @param DeleteRole $deleteRole
     * @return JsonResponse|RedirectResponse
     */
    public function destroy(Role $role, DeleteRole $deleteRole): JsonResponse|RedirectResponse
    {
       $this->authorize('delete', $role);

       try {
            $deleteRole->execute($role);

           return response()->json([
               'message' => "Role deleted successfully",
           ]);
       } catch (Throwable $e) {
           report($e);

           return back()->withErrors($e->getMessage());
       }
    }
}
