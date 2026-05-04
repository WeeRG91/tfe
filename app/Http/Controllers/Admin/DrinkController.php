<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\Drink\Commands\CreateDrink;
use App\Actions\Admin\Drink\Commands\DeleteDrink;
use App\Actions\Admin\Drink\Commands\ForceDeleteDrink;
use App\Actions\Admin\Drink\Commands\RestoreDrink;
use App\Actions\Admin\Drink\Commands\ToggleDrinkAvailability;
use App\Actions\Admin\Drink\Commands\UpdateDrink;
use App\Actions\Admin\Drink\Queries\GetDrinkForEdit;
use App\Actions\Admin\Drink\Queries\GetDrinkFormData;
use App\Actions\Admin\Drink\Queries\GetPaginatedDrinks;
use App\Enums\DrinkCategoryEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Drink\DrinkCreateRequest;
use App\Http\Requests\Admin\Drink\DrinkUpdateRequest;
use App\Models\Drink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Throwable;

class DrinkController extends Controller
{
    /**
     * @return InertiaResponse
     */
    public function index(): InertiaResponse
    {
        return Inertia::render('admin/drink/Index', [
            'categories' => DrinkCategoryEnum::getCategories(),
        ]);
    }

    /**
     * @param Request $request
     * @param GetPaginatedDrinks $query
     * @return JsonResponse
     */
    public function getDrinks(Request $request, GetPaginatedDrinks $query): JsonResponse
    {
        $drinks = $query->execute($request);

        return response()->json($drinks);
    }

    /**
     * @param GetDrinkFormData $query
     * @return InertiaResponse
     */
    public function create(GetDrinkFormData $query): InertiaResponse
    {
        return Inertia::render('admin/drink/Create', $query->execute());
    }

    /**
     * @param DrinkCreateRequest $request
     * @param CreateDrink $command
     * @return RedirectResponse
     */
    public function store(DrinkCreateRequest $request, CreateDrink $command): RedirectResponse
    {
        try {
            $command->execute(
                $request->validated(),
                $request->file('images') ?? []
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
     * @param Drink $drink
     * @param GetDrinkForEdit $query
     * @param GetDrinkFormData $formData
     * @return InertiaResponse
     */
    public function edit(
        Drink $drink,
        GetDrinkForEdit $query,
        GetDrinkFormData $formData
    ): InertiaResponse
    {
        return Inertia::render('admin/drink/Edit', [
            'drinkToEdit' => $query->execute($drink),
            ...$formData->execute(),
        ]);
    }

    /**
     * @param DrinkUpdateRequest $request
     * @param Drink $drink
     * @param UpdateDrink $command
     * @return RedirectResponse
     */
    public function update(DrinkUpdateRequest $request, Drink $drink, UpdateDrink $command): RedirectResponse
    {
        try {
            $command->execute(
                $drink,
                $request->validated(),
                $request->file('images') ?? []
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
     * @param int $id
     * @param DeleteDrink $command
     * @return JsonResponse
     */
    public function destroy(int $id, DeleteDrink $command): JsonResponse
    {
        $command->execute($id);

        return response()->json([
            'message' => 'Drink successfully moved to bin',
        ]);
    }

    /**
     * @param int $id
     * @param ToggleDrinkAvailability $command
     * @return JsonResponse
     */
    public function available(int $id, ToggleDrinkAvailability $command): JsonResponse
    {
        $drink = $command->execute($id);

        return response()->json([
            'message' => 'Drink availability successfully updated',
            'drink' => $drink,
        ]);
    }

    /**
     * @param int $id
     * @param RestoreDrink $command
     * @return JsonResponse
     */
    public function restore(int $id, RestoreDrink $command): JsonResponse
    {
        $command->execute($id);

        return response()->json([
            'message' => 'Drink successfully restored',
        ]);
    }

    /**
     * @param int $id
     * @param ForceDeleteDrink $command
     * @return JsonResponse
     */
    public function forceDelete(int $id, ForceDeleteDrink $command): JsonResponse
    {
        $command->execute($id);

        return response()->json([
            'message' => 'Drink successfully deleted',
        ]);
    }
}
