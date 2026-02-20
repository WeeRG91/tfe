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
use App\Http\Resources\Admin\DishResource;
use App\Models\Dish;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Throwable;

class DishController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetPaginatedDishes $query): InertiaResponse
    {
        $dishes = $query->execute();

        return Inertia::render('admin/dish/Index', [
            'dishes' => DishResource::collection($dishes),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(GetDishFormData $query): InertiaResponse
    {
        return Inertia::render('admin/dish/Create', $query->execute());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DishCreateRequest $request, CreateDish $command): RedirectResponse
    {
        try {
            $command->execute($request->validated(), $request->ingredients);

            return redirect()
                ->route('dish.index')
                ->with('success', 'Dish created successfully.');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors('error', 'Something went wrong while creating the dish.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Dish $dish, GetDishForEdit $query, GetDishFormData $formData): InertiaResponse
    {
        return Inertia::render('admin/dish/Edit', [
            'dishToEdit' => $query->execute($dish),
            ...$formData->execute(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DishUpdateRequest $request, Dish $dish, UpdateDish $command): RedirectResponse
    {
        try {
            $command->execute($dish, $request->validated(), $request->ingredients);

            return redirect()
                ->route('dish.index')
                ->with('success', 'Dish updated successfully.');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors('error', 'Something went wrong while updating the dish.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dish $dish, DeleteDish $command): RedirectResponse
    {
        try {
            $command->execute($dish);

            return redirect()
                ->route('dish.index')
                ->with('success', 'Dish deleted successfully.');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors('error', 'Something went wrong while deleting the dish.');
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
            ->route('dish.index')
            ->with('success', 'Dish available successfully.');
    }
}
