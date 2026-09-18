<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\Support\GlobalSearch;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function search(Request $request, GlobalSearch $search): JsonResponse
    {
        $data = $request->validate([
            'query' => ['required', 'string', 'max:255'],
        ]);

        $results = $search->execute($data['query']);

        return response()->json([
            'results' => $results,
        ]);
    }
}
