<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DestroyPushTokenRequest;
use App\Http\Requests\Api\V1\StorePushTokenRequest;
use App\Http\Resources\Api\V1\PushTokenResource;
use App\Models\ExpoPushToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PushTokenController extends Controller
{
    public function store(
        StorePushTokenRequest $request,
    ): JsonResponse
    {
        $validated = $request->validated();

        $pushToken = ExpoPushToken::query()
            ->firstOrNew([
                'token' => $validated['token'],
            ]);

        $wasRecentlyCreated = !$pushToken->exists;

        $pushToken->fill([
            'user_id' => $request->user()->id,
            'platform' => $validated['platform'],
            'device_name' => $validated['device_name'],
            'last_used_at' => now(),
        ])->save();

        return response()->json([
            'data' => (new PushTokenResource($pushToken))->resolve($request),
        ], $wasRecentlyCreated ? 201 : 200);
    }

    public function destroy(
        DestroyPushTokenRequest $request,
    ): Response
    {
        ExpoPushToken::query()
            ->where('user_id', $request->user()->id)
            ->where('token', $request->validated('token'))
            ->delete();

        return response()->noContent();
    }
}
