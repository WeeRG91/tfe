<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\Drink\Commands\CreateDrink;
use App\Actions\Admin\Drink\Commands\DeleteDrink;
use App\Actions\Admin\Drink\Commands\ToggleDrinkAvailability;
use App\Actions\Admin\Drink\Commands\UpdateDrink;
use App\Actions\Admin\Drink\Queries\GetDrinkForEdit;
use App\Actions\Admin\Drink\Queries\GetDrinkFormData;
use App\Actions\Admin\Drink\Queries\GetPaginatedDrinks;
use App\Enums\DrinkCategoryEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DrinkCreateRequest;
use App\Http\Requests\Admin\DrinkUpdateRequest;
use App\Models\Drink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class DrinkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetPaginatedDrinks $query): Response
    {
        return Inertia::render('admin/drink/Index', [
            'drinks' => $query->execute(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(GetDrinkFormData $query): Response
    {
        return Inertia::render('admin/drink/Create', $query->execute());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DrinkCreateRequest $request, CreateDrink $command): RedirectResponse
    {
        try {
            $command->execute(
                $request->validated(),
                $request->file('images')
            );

            return redirect()
                ->route('drink.index');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors(['message' => 'Something went wrong while creating the drink']);
        }

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(
        Drink $drink,
        GetDrinkForEdit $query,
        GetDrinkFormData $formData
    ): Response
    {
        return Inertia::render('admin/drink/Edit', [
            'drinkToEdit' => $query->execute($drink),
            ...$formData->execute(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DrinkUpdateRequest $request, Drink $drink, UpdateDrink $command): RedirectResponse
    {
        try {
            $command->execute(
                $drink,
                $request->validated(),
                $request->file('images')
            );

            return redirect()
                ->route('drink.index');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors(['message' => 'Something went wrong while updating the drink']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Drink $drink, DeleteDrink $command): RedirectResponse
    {
        try {
            $command->execute($drink);

            return redirect()
                ->route('drink.index');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors(['message' => 'Something went wrong while deleting the drink']);
        }
    }

    /**
     * @param Drink $drink
     * @param ToggleDrinkAvailability $command
     * @return RedirectResponse
     */
    public function available(Drink $drink, ToggleDrinkAvailability $command): RedirectResponse
    {
        $command->execute($drink);

        return redirect()->route('drink.index');
    }
}
