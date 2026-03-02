<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\Meat\Commands\CreateMeat;
use App\Actions\Admin\Meat\Queries\GetPaginatedMeats;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MeatCreateRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class MeatController extends Controller
{
    /**
     * @param GetPaginatedMeats $query
     * @return Response
     */
    public function index(GetPaginatedMeats $query): Response
    {
        return Inertia::render('admin/meat/Index', [
            'meats' => $query->execute(),
        ]);
    }

    /**
     * @return Response
     */
    public function create(): Response
    {
        return Inertia::render('admin/meat/Create');
    }

    /**
     * @param MeatCreateRequest $request
     * @param CreateMeat $command
     * @return RedirectResponse
     */
    public function store(MeatCreateRequest $request, CreateMeat $command): RedirectResponse
    {
        try {
            $command->execute(
                $request->validated(),
                $request->file('images'),
            );

            return redirect()
                ->route('meat.index');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors(['message' => 'Something went wrong while creating the meat']);
        }
    }
}
