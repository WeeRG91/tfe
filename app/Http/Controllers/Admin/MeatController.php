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
    /**
     * @return InertiaResponse
     */
    public function index(): InertiaResponse
    {
        $this->authorize('viewAny', Meat::class);

        return Inertia::render('admin/meat/Index');
    }

    /**
     * @param Request $request
     * @param GetPaginatedMeats $query
     * @return JsonResponse
     */
    public function getMeats(Request $request, GetPaginatedMeats $query)
    {
        $this->authorize('viewAny', Meat::class);

        $meats = $query->execute($request);

        return response()->json($meats);
    }

    /**
     * @return InertiaResponse
     */
    public function create(): InertiaResponse
    {
        $this->authorize('create', Meat::class);

        return Inertia::render('admin/meat/Create');
    }

    /**
     * @param MeatCreateRequest $request
     * @param CreateMeat $command
     * @return RedirectResponse
     */
    public function store(MeatCreateRequest $request, CreateMeat $command): RedirectResponse
    {
        $this->authorize('create', Meat::class);

        try {
            $command->execute(
                $request->validated(),
                $request->file('images') ?? [],
            );

            return redirect()
                ->route('admin.meat.index');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors(['message' => 'Something went wrong while creating the meat']);
        }
    }

    /**
     * @param MeatCreateRequest $request
     * @param CreateMeat $command
     * @return RedirectResponse
     */
    public function quickCreate(
        MeatCreateRequest $request,
        CreateMeat $command
    ): RedirectResponse
    {
        $this->authorize('create', Meat::class);

        try {
            $meat = $command->execute(
                $request->validated(),
                $request->file('images') ?? [],
            );

            return back()->with([
                'createdMeat' => [
                    'value' => $meat->id,
                    'label' => $meat->name,
                ],
            ]);
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors(['message' => 'Something went wrong while creating the meat']);
        }
    }

    /**
     * @param Meat $meat
     * @param GetMeatForEdit $query
     * @return InertiaResponse
     */
    public function edit(Meat $meat, GetMeatForEdit $query): InertiaResponse
    {
        $this->authorize('update', $meat);

        return Inertia::render('admin/meat/Edit', [
            'meatToEdit' => $query->execute($meat),
        ]);
    }

    /**
     * @param MeatUpdateRequest $request
     * @param Meat $meat
     * @param UpdateMeat $command
     * @return RedirectResponse
     */
    public function update(
        MeatUpdateRequest $request,
        Meat $meat,
        UpdateMeat $command
    ): RedirectResponse
    {
        $this->authorize('update', $meat);

        try {
            $command->execute(
                $meat,
                $request->validated(),
                $request->file('images') ?? [],
            );

            return redirect()
                ->route('admin.meat.index');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors(['message' => 'Something went wrong while updating the meat']);
        }
    }

    /**
     * @param Meat $meat
     * @param RestoreMeat $command
     * @return JsonResponse
     */
    public function restore(Meat $meat, RestoreMeat $command): JsonResponse
    {
        $this->authorize('restore', $meat);

        $command->execute($meat->id);

        return response()->json([
            'message' => 'Meat successfully restored',
        ]);
    }

    /**
     * @param Meat $meat
     * @param DeleteMeat $command
     * @return JsonResponse
     */
    public function destroy(Meat $meat, DeleteMeat $command): JsonResponse
    {
        $this->authorize('delete', $meat);

        $command->execute($meat->id);

        return response()->json([
            'message' => 'Meat successfully moved to bin',
        ]);
    }

    /**
     * @param Meat $meat
     * @param ForceDeleteMeat $command
     * @return JsonResponse
     */
    public function forceDelete(Meat $meat, ForceDeleteMeat $command): JsonResponse
    {
        $this->authorize('delete', $meat);

        $command->execute($meat->id);

        return response()->json([
            'message' => 'Meat successfully deleted',
        ]);
    }
}
