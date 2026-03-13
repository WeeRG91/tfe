<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Support\GlobalSearch;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    /**
     * @param Request $request
     * @param GlobalSearch $search
     * @return JsonResponse
     */
    public function search(Request $request, GlobalSearch $search): JsonResponse
    {
        $query = $request->get('query');

        $results = $search->execute($query);

        return response()->json([
            'results' => $results,
        ]);
    }
}
