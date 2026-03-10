<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\Ingredient\Commands\CreateIngredient;
use App\Actions\Admin\Ingredient\Commands\DeleteIngredient;
use App\Actions\Admin\Ingredient\Commands\UpdateIngredient;
use App\Actions\Admin\Ingredient\Queries\GetIngredientForEdit;
use App\Actions\Admin\Ingredient\Queries\GetIngredientFormData;
use App\Actions\Admin\Ingredient\Queries\GetPaginatedIngredients;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IngredientCreateRequest;
use App\Http\Requests\Admin\IngredientUpdateRequest;
use App\Models\Allergen;
use App\Models\Ingredient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class IngredientController extends Controller
{
    /**
     * @return Response
     */
    public function index(): Response
    {
        $allergens = Allergen::all()->map(fn ($allergen) => [
            'value' => $allergen->id,
            'label' => $allergen->name,
        ]);

        return Inertia::render('admin/ingredient/Index', [
            'allergens' => $allergens,
        ]);
    }

    /**
     * @param Request $request
     * @param GetPaginatedIngredients $query
     * @return JsonResponse
     */
    public function getIngredients(Request $request, GetPaginatedIngredients $query): JsonResponse
    {
        $ingredients = $query->execute($request);

        return response()->json($ingredients);
    }

    /**
     * @param GetIngredientFormData $query
     * @return Response
     */
    public function create(GetIngredientFormData $query): Response
    {
        return Inertia::render('admin/ingredient/Create', $query->execute());
    }

    /**
     * @param IngredientCreateRequest $request
     * @param CreateIngredient $command
     * @return RedirectResponse
     */
    public function store(
        IngredientCreateRequest $request,
        CreateIngredient $command
    ): RedirectResponse
    {
        try {
            $command->execute(
                $request->validated(),
                $request->file('images') ?? []
            );

            return redirect()
                ->route('ingredient.index');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors(['message' => 'Something went wrong while creating the ingredient']);
        }
    }

    /**
     * @param IngredientCreateRequest $request
     * @param CreateIngredient $command
     * @return RedirectResponse
     */
    public function quickCreate(
        IngredientCreateRequest $request,
        CreateIngredient $command
    ): RedirectResponse
    {
        try {
            $ingredient = $command->execute(
                $request->validated(),
                $request->file('images') ?? []
            );

            return back()->with([
                'createdIngredient' => [
                    'value' => $ingredient->id,
                    'label' => $ingredient->name,
                ],
            ]);
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors(['message' => 'Something went wrong while creating the ingredient']);
        }
    }

    /**
     * @param Ingredient $ingredient
     * @param GetIngredientFormData $formData
     * @param GetIngredientForEdit $query
     * @return Response
     */
    public function edit(
        Ingredient $ingredient,
        GetIngredientFormData $formData,
        GetIngredientForEdit $query
    ): Response
    {
        return Inertia::render('admin/ingredient/Edit', [
            'ingredientToEdit' => $query->execute($ingredient),
            ...$formData->execute(),
        ]);
    }

    /**
     * @param IngredientUpdateRequest $request
     * @param Ingredient $ingredient
     * @param UpdateIngredient $command
     * @return RedirectResponse
     */
    public function update(
        IngredientUpdateRequest $request,
        Ingredient $ingredient,
        UpdateIngredient $command
    ): RedirectResponse
    {
        try {
            $command->execute(
                $ingredient,
                $request->validated(),
                $request->file('images') ?? []
            );

            return redirect()
                ->route('ingredient.index');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors(['message' => 'Something went wrong while updating the ingredient']);
        }
    }

    public function destroy(
        Ingredient $ingredient,
        DeleteIngredient $command
    ): RedirectResponse
    {
        try {
            $command->execute($ingredient);

            return redirect()
                ->route('ingredient.index');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors(['message' => 'Something went wrong while deleting the ingredient']);
        }
    }
}
