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
use App\Http\Requests\Admin\MeatCreateRequest;
use App\Http\Requests\Admin\MeatUpdateRequest;
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
        return Inertia::render('admin/meat/Index');
    }

    /**
     * @param Request $request
     * @param GetPaginatedMeats $query
     * @return JsonResponse
     */
    public function getMeats(Request $request, GetPaginatedMeats $query)
    {
        $meats = $query->execute($request);

        return response()->json($meats);
    }

    /**
     * @return InertiaResponse
     */
    public function create(): InertiaResponse
    {
        return Inertia::render('admin/meat/Create');
    }

    /**
     * @param MeatCreateRequest $request
     * @param CreateMeat $command
     * @return RedirectResponse
     */
    public function store(MeatCreateRequest $request, CreateMeat $command): RedirectResponse
    {
        try {
            $command->execute(
                $request->validated(),
                $request->file('images') ?? [],
            );

            return redirect()
                ->route('meat.index');
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
        try {
            $command->execute(
                $meat,
                $request->validated(),
                $request->file('images') ?? [],
            );

            return redirect()
                ->route('meat.index');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors(['message' => 'Something went wrong while updating the meat']);
        }
    }

    /**
     * @param int $id
     * @param DeleteMeat $command
     * @return JsonResponse
     */
    public function destroy(int $id, DeleteMeat $command): JsonResponse
    {
        $command->execute($id);

        return response()->json([
            'message' => 'Meat successfully moved to bin',
        ]);
    }

    /**
     * @param int $id
     * @param RestoreMeat $command
     * @return JsonResponse
     */
    public function restore(int $id, RestoreMeat $command): JsonResponse
    {
        $command->execute($id);

        return response()->json([
            'message' => 'Meat successfully restored',
        ]);
    }

    /**
     * @param int $id
     * @param ForceDeleteMeat $command
     * @return JsonResponse
     */
    public function forceDelete(int $id, ForceDeleteMeat $command): JsonResponse
    {
        $command->execute($id);

        return response()->json([
            'message' => 'Meat successfully deleted',
        ]);
    }
}
