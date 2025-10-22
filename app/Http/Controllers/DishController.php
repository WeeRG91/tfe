<?php

namespace App\Http\Controllers;

use App\Enums\DishCategoryEnum;
use App\Http\Requests\DishCreateRequest;
use App\Http\Requests\DishUpdateRequest;
use App\Models\Dish;
use App\Models\Ingredient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DishController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dishes = Dish::query()
            ->with(['ingredients', 'images'])
            ->where('deleted_at', null)
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->through(fn($dish) => [
                'id' => $dish->id,
                'name' => $dish->name,
                'image' => $dish->images->first() ? Storage::disk('public')->url($dish->images->first()->path) : Storage::disk('public')->url('images/picture.png'),
                'images' => $dish->images->map(fn($image) => [
                    'id' => $image->id,
                    'path' => Storage::disk('public')->url($image->path),
                ]),
                'description' => $dish->description,
                'ingredients' => $dish->ingredients->map(fn($ingredient) => [
                    'id' => $ingredient->id,
                    'name' => $ingredient->name,
                ]),
                'category' => DishCategoryEnum::getCategory($dish->category),
                'price' => $dish->price,
                'is_available' => $dish->is_available === 1 ? 'Available' : 'Unavailable',
                'created_at' => $dish->created_at->toDateTimeString(),
                'updated_at' => $dish->updated_at->toDateTimeString(),
            ]);

        return Inertia::render('dish/Index', [
            'dishes' => $dishes,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $ingredients = Ingredient::all()->map(fn($ingredient) => [
            'value' => $ingredient->id,
            'label' => $ingredient->name,
        ]);
        return Inertia::render('dish/Create', [
            'ingredients' => $ingredients,
            'categories' => DishCategoryEnum::getCategories(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DishCreateRequest $request)
    {
        $validated = $request->validated();

        $dish = Dish::query()->create($validated);
        $dish->ingredients()->sync($request->ingredients);
        $dish->uploadImage();

        return redirect()->route('dish.index')->with('success', 'Dish created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Dish $dish)
    {
        $dish->load(['ingredients', 'images']);
        $formattedDish = [
            'id' => $dish->id,
            'name' => $dish->name,
            'category' => $dish->category,
            'description' => $dish->description,
            'price' => $dish->price,
            'ingredients' => $dish->ingredients->map(fn($ingredient) => [
                'id' => $ingredient->id,
            ]),
            'images' => $dish->images->map(fn($image) => [
                'id' => $image->id,
                'path' => Storage::disk('public')->url($image->path),
            ]),
        ];

        $ingredients = Ingredient::all()->map(fn($ingredient) => [
            'value' => $ingredient->id,
            'label' => $ingredient->name,
        ]);

        return Inertia::render('dish/Edit', [
            'dishToEdit' => $formattedDish,
            'ingredients' => $ingredients,
            'categories' => DishCategoryEnum::getCategories(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DishUpdateRequest $request, Dish $dish)
    {
        $validated = $request->validated();

        $dish->update($validated);
        $dish->ingredients()->sync($request->ingredients);
        $dish->uploadImage();

        return redirect()->route('dish.index')->with('success', 'Dish updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dish $dish)
    {
        $dish->delete();

        return redirect()->route('dish.index')->with('success', 'Dish deleted successfully.');
    }

    /**
     * @param Dish $dish
     * @return RedirectResponse
     */
    public function available(Dish $dish)
    {
        $dish->is_available = ! $dish->is_available;
        $dish->save();

        return redirect()->route('dish.index')->with('success', 'Dish available successfully.');
    }
}
