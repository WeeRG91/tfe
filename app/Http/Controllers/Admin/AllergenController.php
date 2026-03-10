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
use App\Models\Allergen;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Throwable;

class AllergenController extends Controller
{
    /**
     * @return InertiaResponse
     */
    public function index(): InertiaResponse
    {
        return Inertia::render('admin/allergen/Index');
    }

    /**
     * @param Request $request
     * @param GetPaginatedAllergens $query
     * @return JsonResponse
     */
    public function getAllergens(Request $request, GetPaginatedAllergens $query): JsonResponse
    {
        $allergens = $query->execute($request);

        return response()->json($allergens);
    }

    /**
     * @param GetAllergenFormData $query
     * @return InertiaResponse
     */
    public function create(GetAllergenFormData $query): InertiaResponse
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
     * @return InertiaResponse
     */
    public function edit(
        Allergen $allergen,
        GetAllergenForEdit $query,
        GetAllergenFormData $formData
    ): InertiaResponse
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
