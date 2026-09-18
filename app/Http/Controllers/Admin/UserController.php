<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\User\Commands\CreateUser;
use App\Actions\Admin\User\Commands\DeleteUser;
use App\Actions\Admin\User\Commands\InactivateUser;
use App\Actions\Admin\User\Commands\ReactivateUser;
use App\Actions\Admin\User\Commands\UpdateUser;
use App\Actions\Admin\User\Queries\GetCreateUserData;
use App\Actions\Admin\User\Queries\GetUpdateUserData;
use App\Actions\Admin\User\Queries\GetUser;
use App\Actions\Admin\User\Queries\GetUsers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\CreateUserRequest;
use App\Http\Requests\Admin\User\UpdateUserRequest;
use App\Http\Resources\Admin\Permission\PermissionResource;
use App\Http\Resources\Admin\Role\RoleResource;
use App\Http\Resources\Admin\User\EditUserResource;
use App\Http\Resources\Admin\User\UserDetailResource;
use App\Http\Resources\Admin\User\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class UserController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', User::class);

        return Inertia::render('admin/user/Index');
    }

    public function getUsers(Request $request, GetUsers $getUsers): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $users = $getUsers->execute($request);

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

    public function show(User $user, GetUser $getUser): Response
    {
        $this->authorize('view', $user);

        return Inertia::render('admin/user/Show', [
            'currentUser' => new UserDetailResource(
                $getUser->execute($user)
            ),
        ]);
    }

    public function create(GetCreateUserData $getCreateUserData): Response
    {
        $this->authorize('create', User::class);

        $results = $getCreateUserData->execute();

        return Inertia::render('admin/user/Create', [
            'roles' => RoleResource::collection($results['roles'])->collection,
            'permissions' => PermissionResource::collection($results['permissions'])->collection,
        ]);
    }

    public function store(CreateUserRequest $request, CreateUser $createUser): RedirectResponse
    {
        $this->authorize('create', User::class);

        try {
            $createUser->execute(
                $request->validated()
            );

            return redirect()->route('admin.user.index');
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors([
                'message' => __('messages.resources.user.create_failed'),
            ]);
        }
    }

    public function edit(User $user, GetUpdateUserData $getUpdateUserData): Response
    {
        $this->authorize('update', $user);

        $results = $getUpdateUserData->execute($user);

        return Inertia::render('admin/user/Edit', [
            'userToEdit' => new EditUserResource($results['user']),
            'roles' => RoleResource::collection($results['roles'])->collection,
            'permissions' => PermissionResource::collection($results['permissions'])->collection,
        ]);
    }

    public function update(
        UpdateUserRequest $request,
        User $user,
        UpdateUser $updateUser
    ): RedirectResponse {
        $this->authorize('update', $user);

        try {
            $updateUser->execute(
                $user,
                $request->validated()
            );

            return redirect()->route('admin.user.index');
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors([
                'message' => __('messages.resources.user.update_failed'),
            ]);
        }
    }

    public function inactivate(User $user, InactivateUser $inactivateUser): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $user);

        try {
            $inactivateUser->execute($user);

            return response()->json([
                'message' => __('messages.resources.user.inactivated'),
            ]);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors([
                'message' => __('messages.errors.unexpected'),
            ]);
        }
    }

    public function reactivate(User $user, ReactivateUser $reactivateUser): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $user);

        try {
            $reactivateUser->execute($user);

            return response()->json([
                'message' => __('messages.resources.user.reactivation_link_sent'),
            ]);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors([
                'message' => __('messages.errors.unexpected'),
            ]);
        }
    }

    public function destroy(User $user, DeleteUser $deleteUser): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $user);

        try {
            $deleteUser->execute($user);

            return response()->json([
                'message' => __('messages.resources.user.deleted'),
            ]);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors([
                'message' => __('messages.resources.user.delete_failed'),
            ]);
        }
    }
}
