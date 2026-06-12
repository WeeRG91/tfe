<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\Dish\Commands\CreateDish;
use App\Actions\Admin\Dish\Commands\DeleteDish;
use App\Actions\Admin\Dish\Commands\ForceDeleteDish;
use App\Actions\Admin\Dish\Commands\RestoreDish;
use App\Actions\Admin\Dish\Commands\ToggleDishAvailability;
use App\Actions\Admin\Dish\Commands\updateDish;
use App\Actions\Admin\Dish\Queries\GetDishForEdit;
use App\Actions\Admin\Dish\Queries\GetDishFormData;
use App\Actions\Admin\Dish\Queries\GetPaginatedDishes;
use App\Enums\DishCategoryEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Dish\DishCreateRequest;
use App\Http\Requests\Admin\Dish\DishUpdateRequest;
use App\Models\Dish;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Throwable;

class DishController extends Controller
{
    /**
     * @return InertiaResponse
     */
    public function index(): InertiaResponse
    {
        return Inertia::render('admin/dish/Index', [
            'categories' => DishCategoryEnum::getCategories(),
        ]);
    }

    /**
     * @param Request $request
     * @param GetPaginatedDishes $query
     * @return JsonResponse
     */
    public function getDishes(Request $request, GetPaginatedDishes $query): JsonResponse
    {
        $dishes = $query->execute($request);

        return response()->json($dishes);
    }

    /**
     * @param GetDishFormData $query
     * @return InertiaResponse
     */
    public function create(GetDishFormData $query): InertiaResponse
    {
        return Inertia::render('admin/dish/Create', $query->execute());
    }

    /**
     * @param DishCreateRequest $request
     * @param CreateDish $command
     * @return RedirectResponse
     */
    public function store(DishCreateRequest $request, CreateDish $command): RedirectResponse
    {
        try {
            $command->execute(
                $request->validated(),
                $request->file('images') ?? [],
                $request->meats ?? [],
                $request->ingredients ?? []
            );

            return redirect()
                ->route('admin.dish.index');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors(['message' => 'Something went wrong while creating the dish']);
        }
    }

    /**
     * @param Dish $dish
     * @param GetDishForEdit $query
     * @param GetDishFormData $formData
     * @return InertiaResponse
     */
    public function edit(Dish $dish, GetDishForEdit $query, GetDishFormData $formData): InertiaResponse
    {
        return Inertia::render('admin/dish/Edit', [
            'dishToEdit' => $query->execute($dish),
            ...$formData->execute(),
        ]);
    }

    /**
     * @param DishUpdateRequest $request
     * @param Dish $dish
     * @param updateDish $command
     * @return RedirectResponse
     */
    public function update(DishUpdateRequest $request, Dish $dish, UpdateDish $command): RedirectResponse
    {
        try {
            $command->execute(
                $dish,
                $request->validated(),
                $request->file('images') ?? [],
                $request->meats,
                $request->ingredients
            );

            return redirect()
                ->route('admin.dish.index');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors(['message' => 'Something went wrong while updating the dish']);
        }
    }

    /**
     * @param int $id
     * @param DeleteDish $command
     * @return JsonResponse
     */
    public function destroy(int $id, DeleteDish $command): JsonResponse
    {
        $command->execute($id);

        return response()->json([
            'message' => 'Dish successfully moved to bin',
        ]);
    }

    /**
     * @param int $id
     * @param ToggleDishAvailability $command
     * @return JsonResponse
     */
    public function available(int $id, ToggleDishAvailability $command): JsonResponse
    {
        $dish = $command->execute($id);

        return response()->json([
            'message' => 'Dish availability successfully updated',
            'dish' => $dish,
        ]);
    }

    /**
     * @param int $id
     * @param RestoreDish $command
     * @return JsonResponse
     */
    public function restore(int $id, RestoreDish $command): JsonResponse
    {
        $command->execute($id);

        return response()->json([
            'message' => 'Dish successfully restored',
        ]);
    }

    /**
     * @param int $id
     * @param ForceDeleteDish $command
     * @return JsonResponse
     */
    public function forceDelete(int $id, ForceDeleteDish $command): JsonResponse
    {
        $command->execute($id);

        return response()->json([
            'message' => 'Dish successfully deleted',
        ]);
    }
}
