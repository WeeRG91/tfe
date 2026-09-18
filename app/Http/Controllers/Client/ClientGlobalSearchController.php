<?php

namespace App\Http\Controllers\Client;

use App\Actions\Client\Support\ClientGlobalSearch;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientGlobalSearchController extends Controller
{
    public function search(Request $request, ClientGlobalSearch $search): JsonResponse
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
