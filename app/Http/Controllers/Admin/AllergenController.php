<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\Allergen\Commands\CreateAllergen;
use App\Actions\Admin\Allergen\Commands\DeleteAllergen;
use App\Actions\Admin\Allergen\Commands\ForceDeleteAllergen;
use App\Actions\Admin\Allergen\Commands\RestoreAllergen;
use App\Actions\Admin\Allergen\Commands\UpdateAllergen;
use App\Actions\Admin\Allergen\Queries\GetAllergenForEdit;
use App\Actions\Admin\Allergen\Queries\GetAllergenFormData;
use App\Actions\Admin\Allergen\Queries\GetPaginatedAllergens;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Allergen\AllergenCreateRequest;
use App\Http\Requests\Admin\Allergen\AllergenUpdateRequest;
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
        $this->authorize('viewAny', Allergen::class);

        return Inertia::render('admin/allergen/Index');
    }

    /**
     * @param Request $request
     * @param GetPaginatedAllergens $query
     * @return JsonResponse
     */
    public function getAllergens(Request $request, GetPaginatedAllergens $query): JsonResponse
    {
        $this->authorize('viewAny', Allergen::class);

        $allergens = $query->execute($request);

        return response()->json($allergens);
    }

    /**
     * @param GetAllergenFormData $query
     * @return InertiaResponse
     */
    public function create(GetAllergenFormData $query): InertiaResponse
    {
        $this->authorize('create', Allergen::class);

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
        $this->authorize('create', Allergen::class);

        try {
            $validated = $request->validated();

            $command->execute(
                data: $validated,
                files: $request->file('images', []),
            );

            return redirect()
                ->route('admin.allergen.index');
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
        $this->authorize('update', $allergen);

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
        $this->authorize('update', $allergen);

        try {
            $validated = $request->validated();

            $command->execute(
                allergen: $allergen,
                data:$validated,
                files: $request->file('images', []),
            );

            return redirect()
                ->route('admin.allergen.index');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors(['message' => 'Something went wrong while updating the allergen']);
        }
    }

    /**
     * @param Allergen $allergen
     * @param RestoreAllergen $command
     * @return JsonResponse
     */
    public function restore(Allergen $allergen, RestoreAllergen $command): JsonResponse
    {
        $this->authorize('restore', $allergen);

        $command->execute($allergen->id);

        return response()->json([
            'message' => 'Allergen successfully restored.',
        ]);
    }

    /**
     * @param Allergen $allergen
     * @param DeleteAllergen $command
     * @return JsonResponse
     */
    public function destroy(Allergen $allergen, DeleteAllergen $command): JsonResponse
    {
        $this->authorize('delete', $allergen);

        $command->execute($allergen->id);

        return response()->json([
            'message' => 'Allergen successfully moved to bin',
        ]);
    }

    /**
     * @param Allergen $allergen
     * @param ForceDeleteAllergen $command
     * @return JsonResponse
     */
    public function forceDelete(Allergen $allergen, ForceDeleteAllergen $command): JsonResponse
    {
        $this->authorize('delete', $allergen);

        $command->execute($allergen->id);

        return response()->json([
            'message' => 'Allergen successfully deleted.',
        ]);
    }
}
