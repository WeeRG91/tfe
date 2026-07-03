<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\CreateUserRequest;
use App\Http\Requests\Admin\User\UpdateUserRequest;
use App\Http\Resources\Admin\Permission\PermissionResource;
use App\Http\Resources\Admin\Role\RoleResource;
use App\Http\Resources\Admin\User\EditUserResource;
use App\Http\Resources\Admin\User\UserDetailResource;
use App\Http\Resources\Admin\User\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Throwable;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/user/Index');
    }

    public function getUsers(Request $request)
    {
        $users = User::query()
            ->with('roles')
            ->where('name', '!=', 'Super Admin')
            ->when($request->search, function ($query, $search) {
                $query->where(fn ($q) =>
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                );
            })
            ->orderBy('name')
            ->get();

        return response()->json(UserResource::collection($users));
    }

    public function show(User $user)
    {
        $user->load(['roles', 'roles.permissions', 'permissions', 'loyaltyPointTransactions', 'orders']);

        return Inertia::render('admin/user/Show', [
            'currentUser' => new UserDetailResource($user),
        ]);
    }

    public function create()
    {
        $roles = Role::with('permissions')
            ->whereNot('name', 'Super Admin')
            ->get();

        return Inertia::render('admin/user/Create', [
            'roles' => RoleResource::collection($roles)->collection,
            'permissions' => PermissionResource::collection(Permission::all())->collection,
        ]);
    }

    public function store(CreateUserRequest $request)
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            if ($request->filled('role')) {
                $user->assignRole($request->role);
            }

            if ($request->filled('permissions')) {
                $user->syncPermissions($request->permissions);
            }

            $user->sendEmailVerificationNotification();

            return redirect()->route('admin.user.index');
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors($e->getMessage());
        }
    }

    public function edit(User $user)
    {
        $user->load(['roles', 'roles.permissions', 'permissions',]);

        $roles = Role::with('permissions')
            ->whereNot('name', 'Super Admin')
            ->get();

        return Inertia::render('admin/user/Edit', [
            'userToEdit' => new EditUserResource($user),
            'roles' => RoleResource::collection($roles)->collection,
            'permissions' => PermissionResource::collection(Permission::all())->collection,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            $emailChanged = $user->email !== $request->email;

            $user->name = $request->name;
            $user->email = $request->email;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            if ($emailChanged) {
                $user->email_verified_at = null;
            }

            $user->save();

            if ($request->filled('role')) {
                $user->assignRole($request->role);
            }

            if ($request->filled('permissions')) {
                $user->syncPermissions($request->permissions);
            }

            if ($emailChanged) {
                $user->sendEmailVerificationNotification();
            }

            return redirect()->route('admin.user.index');
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors($e->getMessage());
        }
    }

    public function destroy(User $user)
    {
        try {
            if ($user->hasRole('Super Admin') || $user->name === 'Super Admin') {
                return response()->json([
                    'message' => "You can't delete super admin user.",
                ]);
            }

            $user->syncRoles([]);
            $user->syncPermissions([]);

            $user->delete();

            return response()->json([
                'message' => "Role deleted successfully",
            ]);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors($e->getMessage());
        }
    }
}
