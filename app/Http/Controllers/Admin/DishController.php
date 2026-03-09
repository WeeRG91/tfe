<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\Dish\Commands\CreateDish;
use App\Actions\Admin\Dish\Commands\DeleteDish;
use App\Actions\Admin\Dish\Commands\ToggleDishAvailability;
use App\Actions\Admin\Dish\Commands\updateDish;
use App\Actions\Admin\Dish\Queries\GetDishForEdit;
use App\Actions\Admin\Dish\Queries\GetDishFormData;
use App\Actions\Admin\Dish\Queries\GetPaginatedDishes;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DishCreateRequest;
use App\Http\Requests\Admin\DishUpdateRequest;
use App\Models\Dish;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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
        return Inertia::render('admin/dish/Index');
    }

    /**
     * @param GetPaginatedDishes $query
     * @return JsonResponse
     */
    public function getDishes(GetPaginatedDishes $query): JsonResponse
    {
        $dishes = $query->execute();

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
                $request->meats,
                $request->ingredients
            );

            return redirect()
                ->route('dish.index');
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
                ->route('dish.index');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors(['message' => 'Something went wrong while updating the dish']);
        }
    }

    /**
     * @param Dish $dish
     * @param DeleteDish $command
     * @return RedirectResponse
     */
    public function destroy(Dish $dish, DeleteDish $command): RedirectResponse
    {
        try {
            $command->execute($dish);

            return redirect()
                ->route('dish.index');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors(['message' => 'Something went wrong while deleting the dish']);
        }
    }

    /**
     * @param Dish $dish
     * @param ToggleDishAvailability $command
     * @return RedirectResponse
     */
    public function available(Dish $dish, ToggleDishAvailability $command): RedirectResponse
    {
        $command->execute($dish);

        return redirect()
            ->route('dish.index');
    }
}
