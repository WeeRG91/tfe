<?php

namespace App\Http\Controllers;

use App\Http\Requests\IngredientCreateRequest;
use App\Http\Requests\IngredientUpdateRequest;
use App\Http\Resources\IngredientResource;
use App\Models\Allergen;
use App\Models\Ingredient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class IngredientController extends Controller
{
    /**
     * @return Response
     */
    public function index()
    {
        $ingredients = Ingredient::query()
            ->with(['allergen', 'images'])
            ->where('deleted_at', null)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('ingredient/Index', [
            'ingredients' => IngredientResource::collection($ingredients),
        ]);
    }

    /**
     * @return Response
     */
    public function create()
    {
        $allergens = Allergen::all()->map(fn($allergen) => [
            'value' => $allergen->id,
            'label' => $allergen->name,
        ]);

        return Inertia::render('ingredient/Create', [
            'allergens' => $allergens,
        ]);
    }

    /**
     * @param IngredientCreateRequest $request
     * @return RedirectResponse
     */
    public function store(IngredientCreateRequest $request)
    {
        $validated = $request->validated();

        $ingredient = Ingredient::query()->create($validated);
        $ingredient->uploadImage();

        return redirect()->route('ingredient.index')->with('success', 'Ingredient created successfully.');
    }

    /**
     * @param Ingredient $ingredient
     * @return Response
     */
    public function edit(Ingredient $ingredient)
    {
        $ingredient->load('allergen');
        $formattedIngredient = [
            'id' => $ingredient->id,
            'name' => $ingredient->name,
            'description' => $ingredient->description,
            'allergen' => $ingredient->allergen->id ?? null,
            'images' => $ingredient->images->map(fn($image) => [
                'id' => $image->id,
                'path' => Storage::disk('public')->url($image->path),
            ]),
        ];

        $allergens = Allergen::all()->map(fn($allergen) => [
            'value' => $allergen->id,
            'label' => $allergen->name,
        ]);

        return Inertia::render('ingredient/Edit', [
            'ingredientToEdit' => $formattedIngredient,
            'allergens' => $allergens,
        ]);
    }

    /**
     * @param IngredientUpdateRequest $request
     * @param Ingredient $ingredient
     * @return RedirectResponse
     */
    public function update(IngredientUpdateRequest $request, Ingredient $ingredient)
    {
        $validated = $request->validated();

        $ingredient->update($validated);
        $ingredient->uploadImage();

        return redirect()->route('ingredient.index')->with('success', 'Ingredient updated successfully.');
    }

    public function destroy(Ingredient $ingredient)
    {
        $ingredient->delete();

        return redirect()->route('ingredient.index')->with('success', 'Ingredient deleted successfully.');
    }
}
