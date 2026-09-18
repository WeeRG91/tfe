<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\Meat\Commands\CreateMeat;
use App\Actions\Admin\Meat\Commands\DeleteMeat;
use App\Actions\Admin\Meat\Commands\ForceDeleteMeat;
use App\Actions\Admin\Meat\Commands\RestoreMeat;
use App\Actions\Admin\Meat\Commands\UpdateMeat;
use App\Actions\Admin\Meat\Queries\GetMeatForEdit;
use App\Actions\Admin\Meat\Queries\GetPaginatedMeats;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Meat\MeatCreateRequest;
use App\Http\Requests\Admin\Meat\MeatUpdateRequest;
use App\Models\Meat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Throwable;

class MeatController extends Controller
{
    public function index(): InertiaResponse
    {
        $this->authorize('viewAny', Meat::class);

        return Inertia::render('admin/meat/Index');
    }

    /**
     * @return JsonResponse
     */
    public function getMeats(Request $request, GetPaginatedMeats $query)
    {
        $this->authorize('viewAny', Meat::class);

        $meats = $query->execute($request);

        return response()->json($meats);
    }

    public function create(): InertiaResponse
    {
        $this->authorize('create', Meat::class);

        return Inertia::render('admin/meat/Create');
    }

    public function store(MeatCreateRequest $request, CreateMeat $command): RedirectResponse
    {
        $this->authorize('create', Meat::class);

        try {
            $validated = $request->validated();

            $command->execute(
                data: $validated,
                files: $request->file('images', []),
            );

            return redirect()
                ->route('admin.meat.index');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors(['message' => __('messages.resources.meat.create_failed')]);
        }
    }

    public function quickCreate(
        MeatCreateRequest $request,
        CreateMeat $command
    ): RedirectResponse {
        $this->authorize('create', Meat::class);

        try {
            $validated = $request->validated();

            $meat = $command->execute(
                data: $validated,
                files: $request->file('images', []),
            );

            $translation = $meat->translate(
                app()->getLocale(),
                false
            );

            return back()->with([
                'createdMeat' => [
                    'value' => $meat->id,
                    'label' => $translation?->name ?? '',
                ],
            ]);
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors(['message' => __('messages.resources.meat.create_failed')]);
        }
    }

    public function edit(Meat $meat, GetMeatForEdit $query): InertiaResponse
    {
        $this->authorize('update', $meat);

        return Inertia::render('admin/meat/Edit', [
            'meatToEdit' => $query->execute($meat),
        ]);
    }

    public function update(
        MeatUpdateRequest $request,
        Meat $meat,
        UpdateMeat $command
    ): RedirectResponse {
        $this->authorize('update', $meat);

        try {
            $validated = $request->validated();

            $command->execute(
                meat: $meat,
                data: $validated,
                files: $request->file('images', []),
            );

            return redirect()
                ->route('admin.meat.index');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors(['message' => __('messages.resources.meat.update_failed')]);
        }
    }

    public function restore(Meat $meat, RestoreMeat $command): JsonResponse
    {
        $this->authorize('restore', $meat);

        $command->execute($meat->id);

        return response()->json([
            'message' => __('messages.resources.meat.restored'),
        ]);
    }

    public function destroy(Meat $meat, DeleteMeat $command): JsonResponse
    {
        $this->authorize('delete', $meat);

        $command->execute($meat->id);

        return response()->json([
            'message' => __('messages.resources.meat.moved_to_bin'),
        ]);
    }

    public function forceDelete(Meat $meat, ForceDeleteMeat $command): JsonResponse
    {
        $this->authorize('delete', $meat);

        $command->execute($meat->id);

        return response()->json([
            'message' => __('messages.resources.meat.deleted'),
        ]);
    }
}
