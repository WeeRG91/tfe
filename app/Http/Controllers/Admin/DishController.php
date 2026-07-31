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
        $this->authorize('viewAny', Dish::class);

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
        $this->authorize('viewAny', Dish::class);

        $dishes = $query->execute($request);

        return response()->json($dishes);
    }

    /**
     * @param GetDishFormData $query
     * @return InertiaResponse
     */
    public function create(GetDishFormData $query): InertiaResponse
    {
        $this->authorize('create', Dish::class);

        return Inertia::render('admin/dish/Create', $query->execute());
    }

    /**
     * @param DishCreateRequest $request
     * @param CreateDish $command
     * @return RedirectResponse
     */
    public function store(
        DishCreateRequest $request,
        CreateDish $command
    ): RedirectResponse
    {
        $this->authorize('create', Dish::class);

        try {
            $validated = $request->validated();

            $command->execute(
                data: $validated,
                files: $request->file('images', []),
                meatIds: $validated['meats'] ?? [],
                ingredientIds: $validated['ingredients'] ?? [],
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
        $this->authorize('update', $dish);

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
        $this->authorize('update', $dish);

        try {
            $validated = $request->validated();

            $command->execute(
                dish: $dish,
                data: $validated,
                files: $request->file('images', []),
                meatIds: $validated['meats'] ?? [],
                ingredientIds: $validated['ingredients'] ?? [],
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
     * @param Dish $dish
     * @param ToggleDishAvailability $command
     * @return JsonResponse
     */
    public function available(Dish $dish, ToggleDishAvailability $command): JsonResponse
    {
        $this->authorize('update', $dish);

        $dish = $command->execute($dish->id);

        return response()->json([
            'message' => 'Dish availability successfully updated',
            'dish' => $dish,
        ]);
    }

    /**
     * @param Dish $dish
     * @param RestoreDish $command
     * @return JsonResponse
     */
    public function restore(Dish $dish, RestoreDish $command): JsonResponse
    {
        $this->authorize('update', $dish);

        $command->execute($dish->id);

        return response()->json([
            'message' => 'Dish successfully restored',
        ]);
    }

    /**
     * @param Dish $dish
     * @param DeleteDish $command
     * @return JsonResponse
     */
    public function destroy(Dish $dish, DeleteDish $command): JsonResponse
    {
        $this->authorize('delete', $dish);

        $command->execute($dish->id);

        return response()->json([
            'message' => 'Dish successfully moved to bin',
        ]);
    }

    /**
     * @param Dish $dish
     * @param ForceDeleteDish $command
     * @return JsonResponse
     */
    public function forceDelete(Dish $dish, ForceDeleteDish $command): JsonResponse
    {
        $this->authorize('delete', $dish);

        $command->execute($dish->id);

        return response()->json([
            'message' => 'Dish successfully deleted',
        ]);
    }
}
