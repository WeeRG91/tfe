<?php

namespace App\Http\Controllers;

use App\Models\Allergen;
use App\Models\Dish;
use App\Models\Ingredient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class TrashedController extends Controller
{
    /**
     * @param string $model
     * @param string $type
     * @return array
     */
    private function getTrashedItems(string $model, string $type)
    {
        return $model::onlyTrashed()
            ->with('images')
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'image' => $item->images->first()
                    ? Storage::disk('public')->url($item->images->first()->path)
                    : Storage::disk('public')->url('images/picture.png'),
                'type' => $type,
                'deleted_at' => $item->deleted_at->toDateTimeString(),
            ])
            ->toArray();
    }

    /**
     * @return Response
     */
    public function index()
    {
        $trashedItems = collect([
            ...$this->getTrashedItems(Dish::class, 'Dish'),
            ...$this->getTrashedItems(Ingredient::class, 'Ingredient'),
            ...$this->getTrashedItems(Allergen::class, 'Allergen'),
        ])->sortByDesc('deleted_at')->values();

        return Inertia::render('trashed/Index', [
            'trashedItems' => $trashedItems,
        ]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function restore(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'type' => 'required|string|in:Dish,Ingredient,Allergen',
        ]);

        $modelClass = match ($request->type) {
            'Dish' => Dish::class,
            'Ingredient' => Ingredient::class,
            'Allergen' => Allergen::class,
        };

        $item = $modelClass::onlyTrashed()->find($request->id);

        if (!$item) {
            return redirect()->back()->with('error', 'Item not found or not trashed.');
        }

        $item->restore();

        return redirect()->back()->with('success', "{$item->name} restored successfully.");
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function forceDelete(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'type' => 'required|string|in:Dish,Ingredient,Allergen',
        ]);

        $modelClass = match ($request->type) {
            'Dish' => Dish::class,
            'Ingredient' => Ingredient::class,
            'Allergen' => Allergen::class,
        };

        $item = $modelClass::onlyTrashed()->find($request->id);

        if (!$item) {
            return redirect()->back()->with('error', 'Item not found or not trashed.');
        }

        if ($request->type == 'Dish') {
            $item->ingredients()->detach();
        } elseif ($request->type == 'Ingredient') {
            $item->dish()->detach();
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
