<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\Ingredient\Commands\CreateIngredient;
use App\Actions\Admin\Ingredient\Commands\DeleteIngredient;
use App\Actions\Admin\Ingredient\Commands\ForceDeleteIngredient;
use App\Actions\Admin\Ingredient\Commands\RestoreIngredient;
use App\Actions\Admin\Ingredient\Commands\UpdateIngredient;
use App\Actions\Admin\Ingredient\Queries\GetIngredientForEdit;
use App\Actions\Admin\Ingredient\Queries\GetIngredientFormData;
use App\Actions\Admin\Ingredient\Queries\GetPaginatedIngredients;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Ingredient\IngredientCreateRequest;
use App\Http\Requests\Admin\Ingredient\IngredientUpdateRequest;
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
        $this->authorize('viewAny', Ingredient::class);

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
        $this->authorize('viewAny', Ingredient::class);

        $ingredients = $query->execute($request);

        return response()->json($ingredients);
    }

    /**
     * @param GetIngredientFormData $query
     * @return Response
     */
    public function create(GetIngredientFormData $query): Response
    {
        $this->authorize('create', Ingredient::class);

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
        $this->authorize('create', Ingredient::class);

        try {
            $validated = $request->validated();

            $command->execute(
                data: $validated,
                files: $request->file('images', []),
            );

            return redirect()
                ->route('admin.ingredient.index');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors(['message' => __('messages.resources.ingredient.create_failed')]);
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
        $this->authorize('create', Ingredient::class);

        try {
            $validated = $request->validated();

            $ingredient = $command->execute(
                data: $validated,
                files: $request->file('images', []),
            );

            $translation = $ingredient->translate(
                app()->getLocale(),
                false
            );

            return back()->with([
                'createdIngredient' => [
                    'value' => $ingredient->id,
                    'label' => $translation?->name ?? '',
                ],
            ]);
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors(['message' => __('messages.resources.ingredient.create_failed')]);
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
        $this->authorize('update', $ingredient);

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
        $this->authorize('update', $ingredient);

        try {
            $validated = $request->validated();

            $command->execute(
                ingredient: $ingredient,
                data: $validated,
                files: $request->file('images', []),
            );

            return redirect()
                ->route('admin.ingredient.index');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors(['message' => __('messages.resources.ingredient.update_failed')]);
        }
    }

    /**
     * @param Ingredient $ingredient
     * @param RestoreIngredient $command
     * @return JsonResponse
     */
    public function restore(Ingredient $ingredient, RestoreIngredient $command): JsonResponse
    {
        $this->authorize('restore', $ingredient);

        $command->execute($ingredient->id);

        return response()->json([
            'message' => __('messages.resources.ingredient.restored'),
        ]);
    }

    /**
     * @param Ingredient $ingredient
     * @param DeleteIngredient $command
     * @return JsonResponse
     */
    public function destroy(Ingredient $ingredient, DeleteIngredient $command): JsonResponse
    {
        $this->authorize('delete', $ingredient);

        $command->execute($ingredient->id);

        return response()->json([
            'message' => __('messages.resources.ingredient.moved_to_bin'),
        ]);
    }

    /**
     * @param Ingredient $ingredient
     * @param ForceDeleteIngredient $command
     * @return JsonResponse
     */
    public function forceDelete(Ingredient $ingredient, ForceDeleteIngredient $command): JsonResponse
    {
        $this->authorize('delete', $ingredient);

        $command->execute($ingredient->id);

        return response()->json([
            'message' => __('messages.resources.ingredient.deleted'),
        ]);
    }
}
