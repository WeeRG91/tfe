<?php

namespace App\Http\Controllers;

use App\Http\Requests\AllergenCreateRequest;
use App\Http\Requests\AllergenUpdateRequest;
use App\Models\Allergen;
use App\Models\Ingredient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AllergenController extends Controller
{
    /**
     * @return Response
     */
    public function index()
    {
        $allergens = Allergen::query()
            ->with(['ingredients', 'images'])
            ->where('deleted_at', null)
            ->orderBy('name')
            ->paginate(10)
            ->through(fn($allergen) => [
                'id' => $allergen->id,
                'name' => $allergen->name,
                'description' => $allergen->description,
                'ingredients' => $allergen->ingredients->map(fn($ingredient) => [
                    'id' => $ingredient->id,
                    'name' => $ingredient->name,
                ]),
                'image' => $allergen->images->first() ? Storage::disk('public')->url($allergen->images->first()->path) : Storage::disk('public')->url('images/picture.png'),
                'images' => $allergen->images->map(fn($image) => [
                    'id' => $image->id,
                    'path' => Storage::disk('public')->url($image->path),
                ]),
                'created_at' => $allergen->created_at->toDateTimeString(),
                'updated_at' => $allergen->updated_at->toDateTimeString(),
            ]);

        return Inertia::render('allergen/Index', [
            'allergens' => $allergens,
        ]);
    }

    /**
     * @return Response
     */
    public function create()
    {
        $ingredients = Ingredient::all()->map(fn($ingredient) => [
            'value' => $ingredient->id,
            'label' => $ingredient->name,
        ]);

        return Inertia::render('allergen/Create', [
            'ingredients' => $ingredients,
        ]);
    }

    /**
     * @param AllergenCreateRequest $request
     * @return RedirectResponse
     */
    public function store(AllergenCreateRequest $request)
    {
        $validated = $request->validated();

        $allergen = Allergen::query()->create([
            'name' => $validated['name'],
            'description' => $validated['description'],
        ]);
        $allergen->uploadImage();

        if (!empty($validated['ingredients'])) {
            Ingredient::query()
                ->whereIn('id', $validated['ingredients'])
                ->update(['allergen_id' => $allergen->id]);
        }

        return redirect()->route('allergen.index')->with('success', 'Allergen successfully created.');
    }

    /**
     * @param Allergen $allergen
     * @return Response
     */
    public function edit(Allergen $allergen)
    {
        $allergen->load(['ingredients', 'images']);
        $formattedAllergen = [
            'id' => $allergen->id,
            'name' => $allergen->name,
            'description' => $allergen->description,
            'ingredients' => $allergen->ingredients->map(fn($ingredient) => [
                'id' => $ingredient->id,
            ]),
            'images' => $allergen->images->map(fn($image) => [
                'id' => $image->id,
                'path' => Storage::disk('public')->url($image->path),
            ]),
        ];

        $ingredients = Ingredient::all()->map(fn($ingredient) => [
            'value' => $ingredient->id,
            'label' => $ingredient->name,
        ]);

        return Inertia::render('allergen/Edit', [
            'allergenToEdit' => $formattedAllergen,
            'ingredients' => $ingredients,
        ]);
    }

    /**
     * @param AllergenUpdateRequest $request
     * @param Allergen $allergen
     * @return RedirectResponse
     */
    public function update(AllergenUpdateRequest $request, Allergen $allergen)
    {
        $validated = $request->validated();

        $allergen->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
        ]);
        $allergen->uploadImage();

        Ingredient::query()->where('allergen_id', $allergen->id)->update(['allergen_id' => null]);

        if (!empty($validated['ingredients'])) {
            Ingredient::query()->whereIn('id', $validated['ingredients'])
                ->update(['allergen_id' => $allergen->id]);
        }

        return redirect()->route('allergen.index')->with('success', 'Allergen successfully updated.');
    }

    /**
     * @param Allergen $allergen
     * @return RedirectResponse
     */
    public function destroy(Allergen $allergen)
    {
        $allergen->delete();

        return redirect()->route('allergen.index')->with('success', 'Allergen successfully deleted.');
    }
}
