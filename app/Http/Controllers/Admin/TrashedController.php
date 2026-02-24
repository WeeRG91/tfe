<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\Trashed\Commands\ForceDeleteTrashedItem;
use App\Actions\Admin\Trashed\Commands\RestoreTrashedItem;
use App\Actions\Admin\Trashed\Queries\GetAllTrashedItems;
use App\Enums\TrashTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TrashedRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class TrashedController extends Controller
{
    /**
     * @param GetAllTrashedItems $query
     * @return Response
     */
    public function index(GetAllTrashedItems $query): Response
    {
        return Inertia::render('admin/trashed/Index', [
            'trashedItems' => $query->execute(),
        ]);
    }

    /**
     * @param TrashedRequest $request
     * @param RestoreTrashedItem $command
     * @return RedirectResponse
     */
    public function restore(TrashedRequest $request, RestoreTrashedItem $command): RedirectResponse
    {
        $validated = $request->validated();

        /** @var Model&SoftDeletes $item */
        $item = $command->execute($validated['id'], TrashTypeEnum::from($validated['type']));

        if (!$item) {
            return redirect()->back()->with('error', 'Item not found or not trashed.');
        }

        $item->restore();

        return redirect()->back()->with('success', "{$item->name} restored successfully.");
    }

    /**
     * @param TrashedRequest $request
     * @param ForceDeleteTrashedItem $command
     * @return RedirectResponse
     */
    public function forceDelete(TrashedRequest $request, ForceDeleteTrashedItem $command): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $item = $command->execute($validated['id'], TrashTypeEnum::from($validated['type']));

            if (!$item) {
                return redirect()->back()->with('error', 'Item not found or not trashed.');
            }

            return redirect()->back()->with('success', "{$item->name} deleted successfully.");
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors(['message' => 'Something went wrong while deleting the dish']);
        }
    }
}
