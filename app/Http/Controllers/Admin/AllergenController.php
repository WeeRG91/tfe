<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\Allergen\Commands\CreateAllergen;
use App\Actions\Admin\Allergen\Commands\DeleteAllergen;
use App\Actions\Admin\Allergen\Commands\UpdateAllergen;
use App\Actions\Admin\Allergen\Queries\GetAllergenForEdit;
use App\Actions\Admin\Allergen\Queries\GetAllergenFormData;
use App\Actions\Admin\Allergen\Queries\GetPaginatedAllergens;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AllergenCreateRequest;
use App\Http\Requests\Admin\AllergenUpdateRequest;
use App\Http\Resources\Admin\AllergenResource;
use App\Models\Allergen;
use App\Models\Ingredient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class AllergenController extends Controller
{
    /**
     * @param GetPaginatedAllergens $query
     * @return Response
     */
    public function index(GetPaginatedAllergens $query): Response
    {
        return Inertia::render('admin/allergen/Index', [
            'allergens' => $query->execute(),
        ]);
    }

    /**
     * @param GetAllergenFormData $query
     * @return Response
     */
    public function create(GetAllergenFormData $query): Response
    {
        return Inertia::render('admin/allergen/Create', $query->execute());
    }

    /**
     * @param AllergenCreateRequest $request
     * @param CreateAllergen $command
     * @return RedirectResponse
     */
    public function store(
        AllergenCreateRequest $request,
        CreateAllergen $command
    ): RedirectResponse
    {
        try {
            $command->execute(
                $request->validated(),
                $request->file('images')
            );

            return redirect()
                ->route('allergen.index');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors(['message' => 'Something went wrong while creating the allergen']);
        }
    }

    /**
     * @param Allergen $allergen
     * @param GetAllergenForEdit $query
     * @param GetAllergenFormData $formData
     * @return Response
     */
    public function edit(
        Allergen $allergen,
        GetAllergenForEdit $query,
        GetAllergenFormData $formData
    ): Response
    {
        return Inertia::render('admin/allergen/Edit', [
            'allergenToEdit' => $query->execute($allergen),
            ...$formData->execute(),
        ]);
    }

    /**
     * @param AllergenUpdateRequest $request
     * @param Allergen $allergen
     * @param UpdateAllergen $command
     * @return RedirectResponse
     */
    public function update(
        AllergenUpdateRequest $request,
        Allergen $allergen,
        UpdateAllergen $command
    ): RedirectResponse
    {
        try {
            $command->execute(
                $allergen,
                $request->validated(),
                $request->file('images') ?? []
            );

            return redirect()
                ->route('allergen.index');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors(['message' => 'Something went wrong while updating the allergen']);
        }
    }

    /**
     * @param Allergen $allergen
     * @param DeleteAllergen $command
     * @return RedirectResponse
     */
    public function destroy(Allergen $allergen, DeleteAllergen $command): RedirectResponse
    {
        try {
            $command->execute($allergen);

            return redirect()
                ->route('allergen.index');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors(['message' => 'Something went wrong while deleting the allergen']);
        }
    }
}
