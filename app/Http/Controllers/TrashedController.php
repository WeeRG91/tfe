<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class TrashedController extends Controller
{
    public function index()
    {
        $trashedDishes = Dish::onlyTrashed()
            ->with('images')
            ->get()
            ->map(fn($dish) => [
                'id' => $dish->id,
                'name' => $dish->name,
                'image' => $dish->images->first() ? Storage::disk('public')->url($dish->images->first()->path) : Storage::disk('public')->url('images/picture.png'),
                'type' => 'Dish',
                'deleted_at' => $dish->deleted_at->toDateTimeString(),
            ]);

        $trashedIngredients = Ingredient::onlyTrashed()
            ->with('images')
            ->get()
            ->map(fn($ingredient) => [
                'id' => $ingredient->id,
                'name' => $ingredient->name,
                'image' => $ingredient->images->first() ? Storage::disk('public')->url($ingredient->images->first()->path) : Storage::disk('public')->url('images/picture.png'),
                'type' => 'Ingredient',
                'deleted_at' => $ingredient->deleted_at->toDateTimeString(),
            ]);

        $trashedItems = $trashedDishes->concat($trashedIngredients)
            ->sortByDesc('deleted_at')
            ->values();

        return Inertia::render('trashed/Index', [
            'trashedItems' => $trashedItems,
        ]);
    }

    public function restore(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'type' => 'required|string|in:Dish,Ingredient',
        ]);

        $modelClass = match ($request->type) {
            'Dish' => Dish::class,
            'Ingredient' => Ingredient::class,
        };

        $item = $modelClass::onlyTrashed()->find($request->id);

        if (!$item) {
            return redirect()->back()->with('error', 'Item not found or not trashed.');
        }

        $item->restore();

        return redirect()->back()->with('success', "{$item->name} restored successfully.");
    }

    public function forceDelete(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'type' => 'required|string|in:Dish,Ingredient',
        ]);

        $modelClass = match ($request->type) {
            'Dish' => Dish::class,
            'Ingredient' => Ingredient::class,
        };

        $item = $modelClass::onlyTrashed()->find($request->id);

        if (!$item) {
            return redirect()->back()->with('error', 'Item not found or not trashed.');
        }

        if ($request->type == 'Dish') {
            $item->ingredients()->detach();
        } elseif ($request->type == 'Ingredient') {
            $item->dish()->detach();
            $item->allergen()->detach();
        }

        if (method_exists($item, 'images')) {
            foreach ($item->images as $image) {
                if (Storage::disk('public')->exists($image->path)) {
                    Storage::disk('public')->delete($image->path);
                }
                $image->delete();
            }
        }

        $item->forceDelete();

        return redirect()->back()->with('success', "{$item->name} deleted successfully.");
    }
}
