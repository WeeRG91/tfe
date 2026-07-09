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
use App\Notifications\AccountActivationNotification;
use App\Notifications\ChangedEmailVerificationNotification;
use App\Notifications\ReactivateAccountNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Throwable;

class UserController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', User::class);

        return Inertia::render('admin/user/Index');
    }

    public function getUsers(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $users = User::withTrashed()
            ->with('roles')
            ->where('name', '!=', 'Super Admin')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                });
            })
            ->when($request->filter, function ($query, $filter) {
                match ($filter) {
                    'active' => $query->withoutTrashed(),
                    'inactive' => $query->onlyTrashed(),
                    default => null,
                };
            })
            ->orderBy('name')
            ->cursorPaginate(8);

        return response()->json([
            'data' => UserResource::collection($users)->collection,
            'path' => $users->path(),
            'per_page' => $users->perPage(),
            'next_cursor' => $users->nextCursor()?->encode(),
            'next_page_url' => $users->nextPageUrl(),
            'prev_cursor' => $users->previousCursor()?->encode(),
            'prev_page_url' => $users->previousPageUrl(),
        ]);
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        $user->load(['roles', 'roles.permissions', 'permissions', 'loyaltyPointTransactions', 'orders']);

        return Inertia::render('admin/user/Show', [
            'currentUser' => new UserDetailResource($user),
        ]);
    }

    public function create()
    {
        $this->authorize('create', User::class);

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
        $this->authorize('create', User::class);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            if ($request->filled('role')) {
                $user->assignRole($request->role);
            }

            if ($request->filled('permissions')) {
                $user->syncPermissions($request->permissions);
            }

            $url = URL::temporarySignedRoute(
                'activate.show',
                now()->addDay(),
                [
                    'user' => $user->id,
                ]
            );

            $user->notify(new AccountActivationNotification($url));

            return redirect()->route('admin.user.index');
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors($e->getMessage());
        }
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

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
        $this->authorize('update', $user);

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
                $url = URL::temporarySignedRoute(
                    'verify-changed-email.store',
                    now()->addDay(),
                    [
                        'user' => $user->id,
                    ]
                );

                $user->notify(new ChangedEmailVerificationNotification($url));
            }

            return redirect()->route('admin.user.index');
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors($e->getMessage());
        }
    }

    public function inactivate(User $user)
    {
        $this->authorize('update', $user);

        try {
            if ($user->hasRole('Super Admin') || $user->name === 'Super Admin') {
                return response()->json([
                    'message' => "You can't inactivate super admin user.",
                ]);
            }

            if ($user->trashed()) {
                return response()->json([
                    'message' => 'User is already inactive.',
                ]);
            }

            $user->forceFill([
                'email_verified_at' => null,
            ])->save();

            $user->delete();

            return response()->json([
                'message' => "User inactivated successfully",
            ]);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors($e->getMessage());
        }
    }

    public function reactivate(User $user)
    {
        $this->authorize('update', $user);

        try {
            if (!$user->trashed()) {
                return response()->json([
                    'message' => 'User is already active.',
                ], 409);
            }

            $url = URL::temporarySignedRoute(
                'reactivate.reactivate',
                now()->addDay(),
                [
                    'user' => $user->id,
                ]
            );

            $user->notify(new ReactivateAccountNotification($url));

            return response()->json([
                'message' => "Reactivation link sent successfully",
            ]);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors($e->getMessage());
        }
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        try {
            if ($user->hasRole('Super Admin') || $user->name === 'Super Admin') {
                return response()->json([
                    'message' => "You can't delete super admin user.",
                ]);
            }

            $user->syncRoles([]);
            $user->syncPermissions([]);

            $user->forceDelete();

            return response()->json([
                'message' => "User deleted permanently",
            ]);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors($e->getMessage());
        }
    }
}
