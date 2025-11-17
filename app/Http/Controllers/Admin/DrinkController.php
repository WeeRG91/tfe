<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DrinkCategoryEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DrinkCreateRequest;
use App\Http\Requests\Admin\DrinkUpdateRequest;
use App\Http\Resources\Admin\DrinkResource;
use App\Models\Drink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use function Termwind\render;

class DrinkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $drinks = Drink::query()
            ->with(['images'])
            ->where('deleted_at', null)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('admin/drink/Index', [
            'drinks' => DrinkResource::collection($drinks),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('admin/drink/Create', [
            'categories' => DrinkCategoryEnum::getCategories(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DrinkCreateRequest $request)
    {
        $validated = $request->validated();

        $drink = Drink::query()->create($validated);
        $drink->uploadImage();

        return redirect()->route('drink.index')->with('success', 'Drink created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Drink $drink)
    {
        $drink->load(['images']);
        $formattedDrink = [
            'id' => $drink->id,
            'name' => $drink->name,
            'description' => $drink->description,
            'category' => $drink->category,
            'price' => $drink->price,
            'images' => $drink->images->map(fn($image) => [
                'id' => $image->id,
                'path' => Storage::disk('public')->url($image->path),
            ]),
        ];

        return Inertia::render('admin/drink/Edit', [
            'drinkToEdit' => $formattedDrink,
            'categories' => DrinkCategoryEnum::getCategories(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DrinkUpdateRequest $request, Drink $drink)
    {
        $validated = $request->validated();

        $drink->update($validated);
        $drink->uploadImage();

        return redirect()->route('drink.index')->with('success', 'Drink updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Drink $drink)
    {
        $drink->delete();

        return redirect()->route('drink.index')->with('success', 'Drink deleted successfully.');
    }

    /**
     * @param Drink $drink
     * @return RedirectResponse
     */
    public function available(Drink $drink)
    {
        $drink->is_available = !$drink->is_available;
        $drink->save();

        return redirect()->route('drink.index')->with('success', 'Drink is available.');
    }
}
