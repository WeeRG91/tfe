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
    public function index(): InertiaResponse
    {
        $this->authorize('viewAny', Drink::class);

        return Inertia::render('admin/drink/Index', [
            'categories' => DrinkCategoryEnum::getCategories(),
        ]);
    }

    public function getDrinks(Request $request, GetPaginatedDrinks $query): JsonResponse
    {
        $this->authorize('viewAny', Drink::class);

        $drinks = $query->execute($request);

        return response()->json($drinks);
    }

    public function create(GetDrinkFormData $query): InertiaResponse
    {
        $this->authorize('create', Drink::class);

        return Inertia::render('admin/drink/Create', $query->execute());
    }

    public function store(DrinkCreateRequest $request, CreateDrink $command): RedirectResponse
    {
        $this->authorize('create', Drink::class);

        try {
            $validated = $request->validated();

            $command->execute(
                data: $validated,
                files: $request->file('images', []),
            );

            return redirect()
                ->route('admin.drink.index');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors(['message' => __('messages.resources.drink.create_failed')]);
        }

    }

    public function edit(
        Drink $drink,
        GetDrinkForEdit $query,
        GetDrinkFormData $formData
    ): InertiaResponse {
        $this->authorize('update', $drink);

        return Inertia::render('admin/drink/Edit', [
            'drinkToEdit' => $query->execute($drink),
            ...$formData->execute(),
        ]);
    }

    public function update(DrinkUpdateRequest $request, Drink $drink, UpdateDrink $command): RedirectResponse
    {
        $this->authorize('update', $drink);

        try {
            $validated = $request->validated();

            $command->execute(
                drink: $drink,
                data: $validated,
                files: $request->file('images', []),
            );

            return redirect()
                ->route('admin.drink.index');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors(['message' => __('messages.resources.drink.update_failed')]);
        }
    }

    public function available(Drink $drink, ToggleDrinkAvailability $command): JsonResponse
    {
        $this->authorize('update', $drink);

        $drink = $command->execute($drink->id);

        return response()->json([
            'message' => __('messages.resources.drink.availability_updated'),
            'drink' => $drink,
        ]);
    }

    public function restore(Drink $drink, RestoreDrink $command): JsonResponse
    {
        $this->authorize('restore', $drink);

        $command->execute($drink->id);

        return response()->json([
            'message' => __('messages.resources.drink.restored'),
        ]);
    }

    public function destroy(Drink $drink, DeleteDrink $command): JsonResponse
    {
        $this->authorize('delete', $drink);

        $command->execute($drink->id);

        return response()->json([
            'message' => __('messages.resources.drink.moved_to_bin'),
        ]);
    }

    public function forceDelete(Drink $drink, ForceDeleteDrink $command): JsonResponse
    {
        $this->authorize('delete', $drink);

        $command->execute($drink->id);

        return response()->json([
            'message' => __('messages.resources.drink.deleted'),
        ]);
    }
}
