<?php

namespace App\Http\Controllers\Client;

use App\Actions\Client\Support\ClientGlobalSearch;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientGlobalSearchController extends Controller
{
    /**
     * @param Request $request
     * @param ClientGlobalSearch $search
     * @return JsonResponse
     */
    public function search(Request $request, ClientGlobalSearch $search): JsonResponse
    {
        $query = $request->get('query');

        $results = $search->execute($query);

        return response()->json([
            'results' => $results,
        ]);
    }
}
