<?php

namespace App\Http\Controllers;

use App\Http\Requests\IngredientCreateRequest;
use App\Http\Requests\IngredientUpdateRequest;
use App\Models\Allergen;
use App\Models\Ingredient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            ->with(['allergen', 'allergen.images', 'images'])
            ->where('deleted_at', null)
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->through(fn($ingredient) => [
                'id' => $ingredient->id,
                'name' => $ingredient->name,
                'allergen' => $ingredient->allergen->name ?? null,
                'allergenImage' => $ingredient->allergen?->images->first()
                    ? Storage::disk('public')->url($ingredient->allergen->images->first()->path)
                    : null,
                'description' => $ingredient->description,
                'image' => $ingredient->images->first()
                    ? Storage::disk('public')->url($ingredient->images->first()->path)
                    : Storage::disk('public')->url('images/picture.png'),
                'images' => $ingredient->images->map(fn($image) => [
                    'id' => $image->id,
                    'path' => Storage::disk('public')->url($image->path),
                ]),
                'created_at' => $ingredient->created_at->toDateTimeString(),
                'updated_at' => $ingredient->updated_at->toDateTimeString(),
            ]);

        return Inertia::render('ingredient/Index', [
            'ingredients' => $ingredients,
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
